FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libzip-dev unzip git libpng-dev \
    && docker-php-ext-install pdo_mysql zip bcmath pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
