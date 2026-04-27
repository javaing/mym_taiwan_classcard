/**
 * Import OtherActivity records from the processed MYM Taiwan CSV.
 *
 * Default mode is dry-run. Use --run to write to Atlas.
 *
 * Usage:
 *   node import-otheractivity-csv.js
 *   node import-otheractivity-csv.js --run
 *   node import-otheractivity-csv.js --file "C:\Users\art.tseng\Desktop\MYM_Taiwan\2026收入明細 - 3-4月_加工後.csv"
 */

require('dotenv').config();

const crypto = require('crypto');
const fs = require('fs');
const path = require('path');
const { MongoClient } = require('mongodb');

const ATLAS_URI = process.env.ATLAS_URI;
const COLLECTION_NAME = 'OtherActivity';
const DEFAULT_FILE = path.join(
  process.env.USERPROFILE || 'C:\\Users\\art.tseng',
  'Desktop',
  'MYM_Taiwan',
  '2026收入明細 - 3-4月_加工後.csv',
);
const EXCLUDED_FILES = new Set(['2026收入明細 - 1-2月_加工後.csv']);

function parseArgs(argv) {
  const result = {
    dryRun: !argv.includes('--run'),
    files: [],
  };

  for (let i = 0; i < argv.length; i++) {
    const arg = argv[i];
    if (arg === '--file') {
      const file = argv[i + 1];
      if (!file) {
        throw new Error('缺少 --file 後方的 CSV 路徑');
      }
      result.files.push(file);
      i++;
    }
  }

  if (!result.files.length) {
    result.files.push(DEFAULT_FILE);
  }

  return result;
}

function parseCsv(text) {
  const rows = [];
  let row = [];
  let cell = '';
  let inQuotes = false;

  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    const next = text[i + 1];

    if (char === '"') {
      if (inQuotes && next === '"') {
        cell += '"';
        i++;
      } else {
        inQuotes = !inQuotes;
      }
      continue;
    }

    if (char === ',' && !inQuotes) {
      row.push(cell);
      cell = '';
      continue;
    }

    if ((char === '\n' || char === '\r') && !inQuotes) {
      if (char === '\r' && next === '\n') i++;
      row.push(cell);
      rows.push(row);
      row = [];
      cell = '';
      continue;
    }

    cell += char;
  }

  if (cell.length || row.length) {
    row.push(cell);
    rows.push(row);
  }

  return rows;
}

function parseAmount(value) {
  const normalized = String(value || '').replace(/,/g, '').trim();
  if (!normalized) return null;
  const amount = Number(normalized);
  if (!Number.isFinite(amount)) return null;
  return amount;
}

function makeImportKey(doc) {
  const raw = [doc['日期'], doc['姓名'], doc['金額'], doc['事由'], doc['備註'] || ''].join('|');
  return crypto.createHash('sha256').update(raw).digest('hex');
}

function naturalFilter(doc, rawAmount) {
  return {
    $or: [
      { importKey: doc.importKey },
      {
        '日期': doc['日期'],
        '姓名': doc['姓名'],
        '金額': doc['金額'],
        '事由': doc['事由'],
        '備註': doc['備註'] || '',
      },
      {
        '日期': doc['日期'],
        '姓名': doc['姓名'],
        '金額': rawAmount,
        '事由': doc['事由'],
        '備註': doc['備註'] || '',
      },
    ],
  };
}

function readCsvRecords(filePath) {
  const baseName = path.basename(filePath);
  if (EXCLUDED_FILES.has(baseName)) {
    throw new Error(`已排除不匯入的檔案: ${baseName}`);
  }

  const text = fs.readFileSync(filePath, 'utf8').replace(/^\uFEFF/, '');
  const rows = parseCsv(text);
  if (rows.length < 3) {
    throw new Error(`${filePath} 沒有足夠列數，預期第 1 列說明、第 2 列欄名、第 3 列起資料`);
  }

  const header = rows[1].map((value) => value.trim());
  const records = [];
  const skipped = [];

  rows.slice(2).forEach((row, index) => {
    const lineNumber = index + 3;
    const item = Object.fromEntries(header.map((key, col) => [key, (row[col] || '').trim()]));
    const rawAmount = item['金額'];
    const amount = parseAmount(rawAmount);

    if (!item['日期'] && !item['姓名'] && !rawAmount && !item['事由']) {
      return;
    }

    if (!item['日期'] || !item['姓名'] || amount === null || !item['事由']) {
      skipped.push({ lineNumber, item, reason: '缺少日期、姓名、金額或事由' });
      return;
    }

    const doc = {
      '日期': item['日期'],
      '姓名': item['姓名'],
      '金額': amount,
      '事由': item['事由'],
      '備註': item['備註'] || '',
      importKey: null,
      importSource: baseName,
      updatedAt: new Date(),
    };
    doc.importKey = makeImportKey(doc);

    records.push({ lineNumber, doc, rawAmount });
  });

  return { filePath, records, skipped };
}

async function main() {
  if (!ATLAS_URI) {
    console.error('請在 .env 設定 ATLAS_URI');
    process.exit(1);
  }

  const options = parseArgs(process.argv.slice(2));
  const inputs = options.files.map((file) => readCsvRecords(path.resolve(file)));
  const records = inputs.flatMap((input) => input.records);
  const skipped = inputs.flatMap((input) => input.skipped);

  console.log(options.dryRun ? 'Dry-run 模式，不會寫入 Atlas' : '寫入模式，將 upsert 到 Atlas');
  console.log('Collection:', COLLECTION_NAME);
  console.log('CSV 檔案:');
  inputs.forEach((input) => console.log(`  ${input.filePath}`));
  console.log('');
  console.log('可匯入資料:', records.length, '筆');
  console.log('略過資料:', skipped.length, '筆');

  const client = new MongoClient(ATLAS_URI);
  try {
    await client.connect();
    const db = client.db();
    const collection = db.collection(COLLECTION_NAME);

    let wouldInsert = 0;
    let wouldUpdate = 0;
    const operations = [];

    for (const record of records) {
      const filter = naturalFilter(record.doc, record.rawAmount);
      const existing = await collection.findOne(filter, { projection: { _id: 1 } });
      if (existing) {
        wouldUpdate++;
      } else {
        wouldInsert++;
      }

      operations.push({
        updateOne: {
          filter,
          update: {
            $set: record.doc,
            $setOnInsert: { createdAt: new Date() },
          },
          upsert: true,
        },
      });
    }

    console.log('預計新增:', wouldInsert, '筆');
    console.log('預計更新:', wouldUpdate, '筆');

    if (records.length) {
      console.log('\n前 5 筆預覽:');
      records.slice(0, 5).forEach((record) => {
        console.log(`  L${record.lineNumber}`, JSON.stringify(record.doc));
      });
    }

    if (skipped.length) {
      console.log('\n略過資料預覽:');
      skipped.slice(0, 10).forEach((item) => {
        console.log(`  L${item.lineNumber} ${item.reason}`, JSON.stringify(item.item));
      });
    }

    if (options.dryRun) {
      console.log('\nDry-run 完成。確認無誤後可加 --run 寫入。');
      return;
    }

    if (operations.length) {
      const result = await collection.bulkWrite(operations, { ordered: false });
      console.log('\n寫入完成');
      console.log('upserted:', result.upsertedCount);
      console.log('modified:', result.modifiedCount);
      console.log('matched:', result.matchedCount);
    } else {
      console.log('\n沒有可寫入資料');
    }
  } finally {
    await client.close();
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
