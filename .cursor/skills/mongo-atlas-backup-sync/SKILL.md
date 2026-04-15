---
name: mongo-atlas-backup-sync
description: >-
  Backs up MongoDB Atlas MYMTW_CardInfo to local JSONL, restores from backup,
  syncs remote MongoDB to Atlas without duplicates, counts documents, and drops
  Atlas DB when confirmed. Use when the user mentions Atlas backup, MYMTW_CardInfo,
  mongo-sync, MongoDB restore, sync to Atlas, or count Atlas collections.
---

# MongoDB Atlas 備份與同步（MYMTW_CardInfo）

專案內工具目錄：`mongo-sync/`（Node.js）。所有指令在 **`mongo-sync`** 目錄執行，並需 **`mongo-sync/.env`**（勿提交；已列於 `mongo-sync/.gitignore`）。

## 前置

1. `cd mongo-sync && npm install`
2. 複製 `.env.example` 為 `.env`，設定：
   - **`ATLAS_URI`**：`mongodb+srv://帳號:密碼@cluster/.../MYMTW_CardInfo`（密碼含 `$` 須寫成 `%24`）
   - **`REMOTE_URI`**：僅在執行 `npm run sync` 時需要；若遠端需認證，格式為 `mongodb://帳號:密碼@主機:27017/MYMTW_CardInfo`

## 指令對照

| 目的 | 指令 |
|------|------|
| 備份 Atlas 到本機 `backup/<DB>_<時間>/` | `npm run backup` |
| 還原備份到 Atlas（upsert） | `node restore-from-backup.js ./backup/<資料夾> [--dry-run]` |
| 遠端 → Atlas 增量同步 | `npm run sync` |
| 統計各 collection 筆數 | `npm run count` |
| 刪除 Atlas 整個 database | `node drop-atlas-data.js --confirm`（危險） |

## 行為要點

- **備份**：每 collection 一個 `.jsonl`（EJSON 每行一筆），含 `_backup_meta.json`。
- **同步**：進度寫在 Atlas 的 `_sync_meta`；無 `updatedAt` 時用 `_id` 時間；若 lastSync 落在未來會改存為現在（見 `sync-to-atlas.js`）。
- **還原**：會寫入 `.env` 的 `ATLAS_URI` 所指的 DB；還原前務必 `--dry-run` 確認路徑。

## 詳細說明

完整步驟與環境變數見 [mongo-sync/README.md](../../../mongo-sync/README.md)。

## Agent 注意

- 不要將真實密碼寫入 repo；僅使用 `.env` 或使用者本機環境變數。
- 執行 `drop` 或大量 `restore` 前應確認使用者意圖。
- Windows 路徑在說明給使用者時可用反斜線；本 skill 內路徑以專案相對路徑 `mongo-sync/` 為準。
