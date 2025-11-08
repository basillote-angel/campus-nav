FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y git unzip libonig-dev libzip-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo_mysql bcmath zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD php artisan serve --host 0.0.0.0 --port 10000

