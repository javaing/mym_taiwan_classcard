/**
 * 從遠端 MongoDB 同步至 MongoDB Atlas，只處理「尚未同步過」的資料
 * 使用方式：在 mongo-sync 目錄下 npm install && npm run sync
 * 請先複製 .env.example 為 .env 並填入 REMOTE_URI、ATLAS_URI
 * 密碼含 $ 請改為 %24（例如 CardKuro$MYM → CardKuro%24MYM）
 */

require('dotenv').config();
const { MongoClient, ObjectId } = require('mongodb');

const REMOTE_URI = process.env.REMOTE_URI;
const ATLAS_URI = process.env.ATLAS_URI;
const COLLECTIONS = process.env.COLLECTIONS
  ? process.env.COLLECTIONS.split(',').map((s) => s.trim()).filter(Boolean)
  : null;
const TIME_FIELD = process.env.TIME_FIELD || 'updatedAt';

const META_COLLECTION = '_sync_meta';
const META_KEY_LAST = 'lastSyncTime';

if (!REMOTE_URI || !ATLAS_URI) {
  console.error('請設定 .env 的 REMOTE_URI 與 ATLAS_URI');
  process.exit(1);
}

function getTimeFromDoc(doc) {
  if (doc[TIME_FIELD]) return doc[TIME_FIELD];
  if (doc._id && ObjectId.isValid(doc._id)) return doc._id.getTimestamp();
  return null;
}

async function getLastSyncTime(atlasDb) {
  const col = atlasDb.collection(META_COLLECTION);
  const doc = await col.findOne({ key: META_KEY_LAST });
  return doc ? doc.value : null;
}

async function setLastSyncTime(atlasDb, time) {
  const col = atlasDb.collection(META_COLLECTION);
  await col.updateOne(
    { key: META_KEY_LAST },
    { $set: { value: time, updatedAt: new Date() } },
    { upsert: true }
  );
}

async function getRemoteCollections(remoteDb) {
  const list = await remoteDb.listCollections().toArray();
  return list.map((c) => c.name).filter((n) => n !== META_COLLECTION);
}

async function syncCollection(remoteDb, atlasDb, collectionName) {
  const remoteCol = remoteDb.collection(collectionName);
  const atlasCol = atlasDb.collection(collectionName);
  const metaKey = `lastSync_${collectionName}`;

  const metaCol = atlasDb.collection(META_COLLECTION);
  const metaDoc = await metaCol.findOne({ key: metaKey });
  const lastSync = metaDoc ? metaDoc.value : null;

  const query = lastSync ? { [TIME_FIELD]: { $gt: lastSync } } : {};
  let hasTimeField = false;
  const sample = await remoteCol.findOne();
  if (sample && sample[TIME_FIELD] != null) hasTimeField = true;
  if (!hasTimeField && lastSync) {
    console.warn(`  [${collectionName}] 無欄位 "${TIME_FIELD}"，改用 _id 篩選`);
    query._id = { $gt: ObjectId.createFromTime(Math.floor(lastSync.getTime() / 1000)) };
    delete query[TIME_FIELD];
  }

  const cursor = remoteCol.find(query);
  let count = 0;
  let latest = lastSync;
  const batch = [];
  const BATCH_SIZE = 500;

  while (await cursor.hasNext()) {
    const doc = await cursor.next();
    batch.push({
      replaceOne: {
        filter: { _id: doc._id },
        replacement: doc,
        upsert: true,
      },
    });
    const t = getTimeFromDoc(doc);
    if (t && (!latest || (t instanceof Date ? t > latest : new Date(t) > latest))) {
      latest = t instanceof Date ? t : new Date(t);
    }
    count++;
    if (batch.length >= BATCH_SIZE) {
      await atlasCol.bulkWrite(batch);
      batch.length = 0;
    }
  }

  if (batch.length) await atlasCol.bulkWrite(batch);

  // lastSync 若在「未來」（例如 _id 對應到錯誤的系統時間），改為現在，避免之後查不到新資料
  const now = new Date();
  if (latest && latest > now) {
    console.warn(`  [${collectionName}] lastSync 在未來 (${latest})，改存為現在`);
    latest = now;
  }
  if (latest) await metaCol.updateOne(
    { key: metaKey },
    { $set: { value: latest, updatedAt: new Date() } },
    { upsert: true }
  );

  console.log(`  [${collectionName}] 同步 ${count} 筆，lastSync=${latest || '-'}`);
  return count;
}

async function main() {
  const remoteClient = new MongoClient(REMOTE_URI);
  const atlasClient = new MongoClient(ATLAS_URI);

  try {
    await remoteClient.connect();
    await atlasClient.connect();
    const remoteDb = remoteClient.db();
    const atlasDb = atlasClient.db();

    const collections = COLLECTIONS || (await getRemoteCollections(remoteDb));
    console.log('同步 collections:', collections);

    for (const name of collections) {
      try {
        await syncCollection(remoteDb, atlasDb, name);
      } catch (err) {
        console.error(`  [${name}] 錯誤:`, err.message);
      }
    }

    console.log('完成');
  } finally {
    await remoteClient.close();
    await atlasClient.close();
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
