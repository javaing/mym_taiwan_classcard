/**
 * 刪除 MongoDB Atlas MYMTW_CardInfo 內「所有資料」
 * 會 drop 整個 database（所有 collection 與資料都會消失）
 *
 * 使用：cd mongo-sync && node drop-atlas-data.js --confirm
 * 未加 --confirm 時只會顯示將要刪除的 DB 名稱，不會執行
 */

require('dotenv').config();
const { MongoClient } = require('mongodb');

const ATLAS_URI = process.env.ATLAS_URI;
const CONFIRM = process.argv.includes('--confirm');

if (!ATLAS_URI) {
  console.error('請在 .env 設定 ATLAS_URI');
  process.exit(1);
}

async function main() {
  const client = new MongoClient(ATLAS_URI);
  try {
    await client.connect();
    const db = client.db();
    const dbName = db.databaseName;

    if (!CONFIRM) {
      console.log('未加 --confirm，不會執行刪除。');
      console.log('將要刪除的 database:', dbName);
      console.log('若確定要刪除所有資料，請執行：');
      console.log('  node drop-atlas-data.js --confirm');
      return;
    }

    await db.dropDatabase();
    console.log('已刪除 database:', dbName);
  } finally {
    await client.close();
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
