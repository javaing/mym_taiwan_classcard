# MongoDB 同步至 Atlas（Node.js）

從遠端 MongoDB 抓取資料寫入 MongoDB Atlas，**只同步尚未同步過的資料**（依時間或 _id 記錄上次進度）。

## 1. 安裝

```bash
cd mongo-sync
npm install
```

## 2. 設定連線

複製 `.env.example` 為 `.env`，填入：

```env
REMOTE_URI=mongodb://mymtaiwan.eastasia.cloudapp.azure.com:27017/MYMTW_CardInfo
ATLAS_URI=mongodb+srv://masterSakura:你的密碼@cluster0.zywrnwr.mongodb.net/MYMTW_CardInfo
```

**密碼含特殊字元**：在 URI 裡要 URL 編碼，例如 `$` → `%24`。  
若密碼是 `CardKuro$MYM`，請寫成 `CardKuro%24MYM`。

## 3. 執行同步

```bash
npm run sync
```

- 第一次執行：會同步**所有** collection 的資料到 Atlas。
- 之後再執行：只會同步「比上次同步時間新」的資料，不會重複已匯出的。

## 4. 選用環境變數

| 變數 | 說明 |
|------|------|
| `COLLECTIONS` | 只同步這些 collection（逗號分隔）。留空則同步遠端所有 collection（除 `_sync_meta`） |
| `TIME_FIELD` | 用來判斷「新資料」的欄位，預設 `updatedAt`。若沒有此欄位會改用 `_id` 的時間 |

範例只同步兩個 collection：

```env
COLLECTIONS=UserInfo,CardInfo
```

## 5. 進度儲存

腳本會在 Atlas 的 `_sync_meta` collection 裡記錄每個 collection 的上次同步時間，下次執行時只拉新資料。

## 6. 刪除 Atlas 內所有資料（drop database）

**危險操作**，會刪除 MYMTW_CardInfo 整個 database（所有 collection 與資料）。

```bash
# 先看會刪哪個 DB，不執行
node drop-atlas-data.js

# 確定要刪除時才加 --confirm
node drop-atlas-data.js --confirm
```

或：`npm run drop -- --confirm`

## 7. 備份 Atlas MYMTW_CardInfo 到本機

將 Atlas 連線字串（`.env` 的 `ATLAS_URI`）指向的資料庫，**全部 collection** 匯出到本機資料夾：

```bash
npm run backup
```

- 輸出目錄：`mongo-sync/backup/<資料庫名>_<時間戳>/`
- 每個 collection 一個檔案：`CollectionName.jsonl`（每行一筆 JSON，含 ObjectId、Date 等型別的 EJSON 格式）
- 另有 `_backup_meta.json` 記錄匯出時間與 collection 清單

**還原到 Atlas**（會 upsert 到 `.env` 的 `ATLAS_URI` 指向的 DB，請先確認連線）：

```bash
# 先看會處理哪些檔（不寫入）
node restore-from-backup.js ./backup/MYMTW_CardInfo_2026-04-15T12-00-00 --dry-run

# 實際寫入
node restore-from-backup.js ./backup/MYMTW_CardInfo_2026-04-15T12-00-00
```

`backup/` 已列入 `.gitignore`，避免把資料備份誤 push 到 GitHub。

## 8. 修正 Atlas 內 CardID 型別（一次性，選用）

若同步後課卡頁只顯示部分蓋章紀錄，常是 Atlas 裡 `Consume` / `Purchase` 的 `CardID` 被存成數字，而程式預期字串。可將 Atlas 內該欄位統一改為字串：

```bash
# 預覽會改幾筆（不寫入）
npm run fix-cardid

# 實際執行更新
npm run fix-cardid:run
```
