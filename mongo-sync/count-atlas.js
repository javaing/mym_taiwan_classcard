/**
 * 查詢 MongoDB Atlas MYMTW_CardInfo 各 collection 筆數與總筆數
 * 使用：cd mongo-sync && npm run count（需先設定 .env 的 ATLAS_URI）
 */

require('dotenv').config();
const { MongoClient } = require('mongodb');

const ATLAS_URI = process.env.ATLAS_URI;
if (!ATLAS_URI) {
  console.error('請在 .env 設定 ATLAS_URI');
  process.exit(1);
}

async function main() {
  const client = new MongoClient(ATLAS_URI);
  try {
    await client.connect();
    const db = client.db();
    const list = await db.listCollections().toArray();
    const names = list.map((c) => c.name);

    let total = 0;
    console.log('MYMTW_CardInfo 各 collection 筆數：\n');

    for (const name of names) {
      const count = await db.collection(name).countDocuments();
      total += count;
      console.log(`  ${name}: ${count.toLocaleString()} 筆`);
    }

    console.log('\n  總計:', total.toLocaleString(), '筆');
  } finally {
    await client.close();
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
