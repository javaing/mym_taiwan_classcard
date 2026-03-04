# Laravel 7 需 PHP 7.4。使用 Debian 基底（apt-get），勿用 Alpine（apk）。
# 若 Render 仍報 apk/gd 錯誤，請在 Dashboard 使用 Clear build cache & deploy。
FROM php:7.4-fpm-bullseye

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev zip unzip curl \
    libssl-dev pkg-config \
    && rm -rf /var/lib/apt/lists/*

# PHP 擴展：gd、mongodb（PECL 最新版僅支援 PHP 8.1+，7.4 需指定 1.16.x）
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip \
    && pecl install mongodb-1.16.2 \
    && docker-php-ext-enable mongodb

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Nginx：Laravel 使用 public 為 document root
COPY docker/nginx-default.conf /etc/nginx/conf.d/default.conf
RUN rm -f /etc/nginx/sites-enabled/default

COPY docker/supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --no-interaction --optimize-autoloader

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    COMPOSER_ALLOW_SUPERUSER=1

RUN chmod +x /var/www/html/scripts/01-render-deploy.sh

EXPOSE 80

CMD /var/www/html/scripts/01-render-deploy.sh && exec /usr/bin/supervisord -c /etc/supervisord.conf
