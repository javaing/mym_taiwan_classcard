# Render 部署除錯說明

若部署失敗，請到 **Render Dashboard → 你的 Service → Logs**，找到錯誤訊息後對照下表，或把 **完整 Build / Deploy 日誌** 貼給除錯用。

---

## 常見錯誤與處理

### 1. Build 失敗：`composer install` 或 extension 錯誤

| 錯誤關鍵字 | 可能原因 | 處理方式 |
|-----------|----------|----------|
| `Return type of ... Collection::offsetExists` / `ArrayAccess` / `#[\ReturnTypeWillChange]` | **PHP 8 與 Laravel 7 不相容** | 本專案已改為使用 **PHP 7.4** 基礎映像（`php:7.4-fpm-alpine`），請重新部署。 |
| `the requested PHP extension ext-gd` | 本機 PHP 與 Render 環境版本不一致 | 已將 `composer.json` 的 `ext-gd` 改為 `*`；Dockerfile 內已安裝 gd 擴展。 |
| `mongodb extension` / `Extension mongodb` | Docker 映像未安裝 MongoDB 擴展 | 本 repo 的 `Dockerfile` 已加入 `pecl install mongodb`。若仍失敗，日誌中會有 `pecl` 或 `docker-php-ext-enable` 錯誤，可依錯誤訊息再調整。 |
| `composer.lock` not found / 版本不一致 | 未提交 lock 檔或依賴版本不同 | 建議提交 `composer.lock`（可從 `.gitignore` 移除 `composer.lock`），再部署。 |

### 2. Build 失敗：Docker / 檔案相關

| 錯誤關鍵字 | 可能原因 | 處理方式 |
|-----------|----------|----------|
| `build.sh file not found` | Build Command 指向不存在的腳本 | 使用 **Docker** 部署時不要自訂 Build Command，留空即可（由 Dockerfile 負責 build）。 |
| `Cannot find module` (Node) | 前端用 Node 但 Docker 未安裝 | 若需要編譯前端，需在 Dockerfile 中安裝 Node 並執行 `npm ci && npm run production`。 |

### 3. Deploy / 執行階段錯誤

| 錯誤關鍵字 | 可能原因 | 處理方式 |
|-----------|----------|----------|
| `No application encryption key` | `APP_KEY` 未設定 | 在 Render **Environment** 新增 `APP_KEY`，值為 `php artisan key:generate --show` 的輸出（或既有 .env 的 key）。 |
| `502 Bad Gateway` | 服務未正確監聽 port 或未啟動 | 使用本專案 Dockerfile 時，由映像內建 nginx+php-fpm 監聽，無需自訂 Start Command。若曾改過 Start Command，請清空改回預設。 |
| `Connection refused` (MongoDB) | 連線字串或網路不允許 | 確認 `DB_HOST`、`DB_USERNAME`、`DB_PASSWORD`、`DB_DATABASE` 在 Render Environment 已設，且 MongoDB Atlas 的 Network Access 允許 Render 的 IP（或 0.0.0.0/0 測試）。 |

### 4. 環境變數檢查清單（Render Dashboard → Environment）

- `APP_KEY`（必填）
- `APP_URL` = `https://你的服務名.onrender.com`
- `APP_ENV` = `production`
- `APP_DEBUG` = `false`
- `DB_CONNECTION` = `mongodb+srv`
- `DB_HOST`、`DB_DATABASE`、`DB_USERNAME`、`DB_PASSWORD`（與 MongoDB Atlas 一致）

---

## 本專案 Render 設定建議

- **Runtime**：**Docker**
- **Build Command**：留空（由 Dockerfile 負責）
- **Start Command**：留空（使用 Dockerfile 的 `CMD`）
- 環境變數依上表在 Dashboard 設定，勿把 `.env` 或密鑰 commit 進 repo。

若你看到的錯誤不在上表，請把 **Build 或 Runtime 的完整錯誤日誌** 貼出來，方便進一步排查。
