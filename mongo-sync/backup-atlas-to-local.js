/**
 * 將 MongoDB Atlas MYMTW_CardInfo 備份到本機
 * 輸出：mongo-sync/backup/<資料庫名>_<時間戳>/*.jsonl（每行一筆 JSON，還原時較好串流）
 *
 * 使用：cd mongo-sync && npm run backup
 * 需 .env 內 ATLAS_URI
 */

require('dotenv').config();
const { MongoClient } = require('mongodb');
const { EJSON } = require('bson');
const fs = require('fs');
const path = require('path');

const ATLAS_URI = process.env.ATLAS_URI;
if (!ATLAS_URI) {
  console.error('請在 .env 設定 ATLAS_URI');
  process.exit(1);
}

function safeTimestamp() {
  return new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
}

async function main() {
  const client = new MongoClient(ATLAS_URI);
  try {
    await client.connect();
    const db = client.db();
    const dbName = db.databaseName;
    const outDir = path.join(__dirname, 'backup', `${dbName}_${safeTimestamp()}`);
    fs.mkdirSync(outDir, { recursive: true });

    const list = await db.listCollections().toArray();
    const names = list.map((c) => c.name);

    console.log('備份到:', outDir);
    console.log('Collections:', names.length, '\n');

    for (const name of names) {
      const filePath = path.join(outDir, `${name}.jsonl`);
      const writeStream = fs.createWriteStream(filePath, { flags: 'w' });
      const cursor = db.collection(name).find({});
      let count = 0;
      while (await cursor.hasNext()) {
        const doc = await cursor.next();
        // EJSON 可保留 Date、ObjectId 等型別資訊，還原時較準
        writeStream.write(JSON.stringify(EJSON.serialize(doc)) + '\n');
        count++;
        if (count % 5000 === 0) process.stdout.write(`  ${name}: ${count}...\r`);
      }
      writeStream.end();
      await new Promise((resolve, reject) => {
        writeStream.on('finish', resolve);
        writeStream.on('error', reject);
      });
      console.log(`  ${name}: ${count.toLocaleString()} 筆 → ${name}.jsonl`);
    }

    const meta = {
      database: dbName,
      exportedAt: new Date().toISOString(),
      collections: names,
    };
    fs.writeFileSync(path.join(outDir, '_backup_meta.json'), JSON.stringify(meta, null, 2), 'utf8');
    console.log('\n完成。還原請見 README「備份與還原」。');
  } finally {
    await client.close();
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
