FROM php:7.4-fpm-bullseye

RUN apt-get update --fix-missing && apt-get install -y --no-install-recommends \
    nginx git unzip curl \
    libpng-dev libonig-dev libxml2-dev \
    libzip-dev libfreetype6-dev libjpeg62-turbo-dev \
    libssl-dev pkg-config \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

RUN pecl install mongodb-1.16.2 \
    && docker-php-ext-enable mongodb

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY nginx.conf /etc/nginx/sites-available/default

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log

EXPOSE 80

CMD sed -i "s/listen 80/listen ${PORT:-80}/g" /etc/nginx/sites-available/default && \
    service nginx start && \
    php-fpm
