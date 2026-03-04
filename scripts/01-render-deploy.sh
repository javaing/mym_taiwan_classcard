#!/bin/sh
set -e
cd /var/www/html

# Render 預設要求服務監聽 PORT（例如 10000）
NGINX_CONF="/etc/nginx/conf.d/default.conf"
if [ -n "${PORT}" ] && [ "${PORT}" != "80" ]; then
  sed -i "s/listen 80;/listen ${PORT};/" "$NGINX_CONF"
fi

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Creating storage link if missing..."
php artisan storage:link 2>/dev/null || true

# 使用 MongoDB，無需 migrate；若日後有 SQL 再取消註解
# php artisan migrate --force

echo "Render deploy script done."
