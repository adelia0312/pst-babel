FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libgd-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install gd pdo pdo_mysql mbstring zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-scripts --no-interaction

RUN php artisan key:generate
RUN php artisan config:cache

EXPOSE 80