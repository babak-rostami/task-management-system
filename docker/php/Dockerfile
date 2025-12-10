FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl libpq-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
