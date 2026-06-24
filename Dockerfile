FROM php:8.3-fpm-alpine

RUN apk add --no-cache postgresql-dev libzip-dev unzip \
    && docker-php-ext-install pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction
