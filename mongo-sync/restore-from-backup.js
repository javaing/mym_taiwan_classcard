/**
 * 從本機 jsonl 備份還原到 MongoDB（預設寫入 ATLAS_URI 指向的 DB）
 * 使用：node restore-from-backup.js <備份資料夾路徑> [--dry-run]
 *
 * 範例：
 *   node restore-from-backup.js ./backup/MYMTW_CardInfo_2026-04-15T12-00-00
 *   node restore-from-backup.js ./backup/MYMTW_CardInfo_2026-04-15T12-00-00 --dry-run
 */

require('dotenv').config();
const { MongoClient } = require('mongodb');
const { EJSON } = require('bson');
const fs = require('fs');
const path = require('path');
const readline = require('readline');

const ATLAS_URI = process.env.ATLAS_URI;
const backupDir = process.argv[2];
const dryRun = process.argv.includes('--dry-run');

if (!ATLAS_URI) {
  console.error('請在 .env 設定 ATLAS_URI');
  process.exit(1);
}
if (!backupDir || !fs.existsSync(backupDir)) {
  console.error('用法: node restore-from-backup.js <備份資料夾> [--dry-run]');
  process.exit(1);
}

async function restoreCollection(db, filePath, collectionName) {
  if (dryRun) {
    const lines = fs.readFileSync(filePath, 'utf8').split('\n').filter(Boolean).length;
    console.log(`  [dry-run] ${collectionName}: 約 ${lines} 行`);
    return;
  }
  const col = db.collection(collectionName);
  const rl = readline.createInterface({
    input: fs.createReadStream(filePath, { encoding: 'utf8' }),
    crlfDelay: Infinity,
  });
  const batch = [];
  const BATCH = 500;
  let count = 0;
  for await (const line of rl) {
    if (!line.trim()) continue;
    const doc = EJSON.deserialize(JSON.parse(line));
    batch.push({ replaceOne: { filter: { _id: doc._id }, replacement: doc, upsert: true } });
    if (batch.length >= BATCH) {
      await col.bulkWrite(batch);
      count += batch.length;
      batch.length = 0;
    }
  }
  if (batch.length) await col.bulkWrite(batch);
  count += batch.length;
  console.log(`  ${collectionName}: ${count} 筆`);
}

async function main() {
  const abs = path.resolve(backupDir);
  const files = fs.readdirSync(abs).filter((f) => f.endsWith('.jsonl'));
  if (!files.length) {
    console.error('目錄內沒有 .jsonl 檔案');
    process.exit(1);
  }
  console.log(dryRun ? 'Dry-run 模式，不會寫入資料庫' : '將寫入 ATLAS_URI 的資料庫');
  console.log('備份目錄:', abs);

  const client = new MongoClient(ATLAS_URI);
  try {
    await client.connect();
    const db = client.db();
    for (const file of files) {
      const name = file.replace(/\.jsonl$/, '');
      await restoreCollection(db, path.join(abs, file), name);
    }
    console.log('完成');
  } finally {
    await client.close();
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
