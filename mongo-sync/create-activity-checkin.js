/**
 * 建立台中活動報到 collection、欄位驗證與同日防重唯一索引。
 *
 * 使用方式（mongo-sync 目錄）：
 *   node create-activity-checkin.js        # 只檢查，不寫入
 *   node create-activity-checkin.js --run  # 實際建立／更新
 */

require('dotenv').config();
const { MongoClient } = require('mongodb');

const ATLAS_URI = process.env.ATLAS_URI;
const COLLECTION = 'ActivityCheckin';
const isDryRun = !process.argv.includes('--run');

if (!ATLAS_URI) {
  console.error('請設定 .env 的 ATLAS_URI');
  process.exit(1);
}

const validator = {
  $and: [
    {
      $jsonSchema: {
        bsonType: 'object',
        required: [
          'UserID',
          'Branch',
          'ActivityType',
          'ActivityName',
          'Amount',
          'PaymentMethod',
          'BusinessDate',
          'CheckinTime',
          'Source',
          'CreatedAt',
        ],
        properties: {
          UserID: { bsonType: 'string' },
          Branch: { enum: ['taichung'] },
          ActivityType: { enum: ['asana', 'chanting'] },
          ActivityName: { bsonType: 'string' },
          Amount: { bsonType: ['int', 'long', 'double', 'decimal'], minimum: 0 },
          PaymentMethod: { enum: ['cash'] },
          BusinessDate: {
            bsonType: 'string',
            pattern: '^\\d{4}-\\d{2}-\\d{2}$',
          },
          CheckinTime: { bsonType: 'date' },
          Source: { bsonType: 'string' },
          CreatedAt: { bsonType: 'date' },
        },
      },
    },
    {
      $or: [
        {
          ActivityType: 'asana',
          ActivityName: '體位法',
          Amount: 300,
        },
        {
          ActivityType: 'chanting',
          ActivityName: '梵唱',
          Amount: 100,
        },
      ],
    },
  ],
};

async function main() {
  const client = new MongoClient(ATLAS_URI);

  try {
    await client.connect();
    const db = client.db();
    const exists = await db
      .listCollections({ name: COLLECTION }, { nameOnly: true })
      .hasNext();

    console.log('Atlas DB:', db.databaseName);
    console.log('Collection:', COLLECTION, exists ? '已存在' : '尚未建立');

    if (isDryRun) {
      if (exists) {
        const indexes = await db.collection(COLLECTION).indexes();
        console.log('現有 indexes:', indexes.map((index) => index.name).join(', '));
      }
      console.log('預覽模式：加上 --run 才會建立 collection、validator 與唯一索引。');
      return;
    }

    if (!exists) {
      await db.createCollection(COLLECTION, {
        validator,
        validationLevel: 'strict',
        validationAction: 'error',
      });
      console.log('已建立 collection 與 validator。');
    } else {
      await db.command({
        collMod: COLLECTION,
        validator,
        validationLevel: 'strict',
        validationAction: 'error',
      });
      console.log('已更新 collection validator。');
    }

    const indexName = await db.collection(COLLECTION).createIndex(
      {
        Branch: 1,
        UserID: 1,
        ActivityType: 1,
        BusinessDate: 1,
      },
      {
        name: 'uniq_branch_user_activity_business_date',
        unique: true,
      },
    );

    console.log('已建立／確認唯一索引:', indexName);
  } finally {
    await client.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
