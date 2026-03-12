/**
 * 一次性：將 Atlas 內 Consume、Purchase 的 CardID 統一改為字串（與程式運行 5 年的遠端 DB 一致）
 * 使用方式：在 mongo-sync 目錄下
 *   node fix-atlas-cardid-type.js        # 只顯示會改幾筆，不寫入
 *   node fix-atlas-cardid-type.js --run  # 實際執行更新
 */

require('dotenv').config();
const { MongoClient } = require('mongodb');

const ATLAS_URI = process.env.ATLAS_URI;
const COLLECTIONS = ['Consume', 'Purchase'];

if (!ATLAS_URI) {
  console.error('請設定 .env 的 ATLAS_URI');
  process.exit(1);
}

const isDryRun = !process.argv.includes('--run');

async function fixCollection(db, colName) {
  const col = db.collection(colName);
  // 找出 CardID 不是 string 的（BSON type 2 = string）
  const notString = await col.countDocuments({ CardID: { $not: { $type: 'string' } } });
  if (notString === 0) {
    console.log(`  [${colName}] CardID 已是字串，無需更新`);
    return { updated: 0 };
  }
  if (isDryRun) {
    console.log(`  [${colName}] 將有 ${notString} 筆 CardID 改為字串（未執行，請加 --run 才寫入）`);
    return { updated: notString };
  }
  // MongoDB 4.2+ 支援 aggregation pipeline 的 updateMany
  const r = await col.updateMany(
    { CardID: { $not: { $type: 'string' } } },
    [{ $set: { CardID: { $toString: '$CardID' } } }]
  );
  console.log(`  [${colName}] 已將 ${r.modifiedCount} 筆 CardID 改為字串`);
  return { updated: r.modifiedCount };
}

async function main() {
  const client = new MongoClient(ATLAS_URI);
  try {
    await client.connect();
    const db = client.db();
    console.log('Atlas DB:', db.databaseName);
    console.log(isDryRun ? '（預覽模式，加 --run 才會寫入）\n' : '（執行更新）\n');

    let total = 0;
    for (const colName of COLLECTIONS) {
      const { updated } = await fixCollection(db, colName);
      total += updated;
    }
    console.log('\n完成。' + (isDryRun ? ' 要實際更新請執行: node fix-atlas-cardid-type.js --run' : ''));
  } finally {
    await client.close();
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
