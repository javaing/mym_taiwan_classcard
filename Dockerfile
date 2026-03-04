# Laravel 7 需在 PHP 7.4 執行（PHP 8 會與 ArrayAccess 回傳型別不相容）
FROM php:7.4-fpm-alpine

RUN apk add --no-cache nginx supervisor

# PHP 擴展：gd（composer ext-gd）、mongodb（jenssegers/mongodb）
RUN apk add --no-cache \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    autoconf g++ make \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apk del autoconf g++ make libpng-dev libjpeg-turbo-dev freetype-dev

# Composer（官方 php 映像未內建）
RUN apk add --no-cache curl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Nginx：Laravel 使用 public 為 document root
COPY docker/nginx-default.conf /etc/nginx/http.d/default.conf
RUN rm -f /etc/nginx/conf.d/default.conf

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
