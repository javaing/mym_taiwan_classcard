#!/bin/bash
set -e

cd /var/www/html

PORT=${PORT:-10000}
sed -i "s/listen 80/listen ${PORT}/g" /etc/nginx/sites-available/default

php artisan package:discover --ansi
php artisan config:clear
php artisan cache:clear

touch /var/www/html/storage/logs/laravel.log
chown www-data:www-data /var/www/html/storage/logs/laravel.log
tail -f /var/www/html/storage/logs/laravel.log &

php-fpm -D

echo "Starting nginx on port ${PORT}..."
nginx -g "daemon off;"
