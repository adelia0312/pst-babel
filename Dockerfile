FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libgd-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd pdo pdo_mysql mbstring zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-scripts --no-interaction --ignore-platform-req=ext-gd

EXPOSE 80
CMD ["apache2-foreground"]