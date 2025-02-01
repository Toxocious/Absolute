FROM php:8.0-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libxpm-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo pdo_mysql gd opcache \
    && pecl install xdebug-3.2.0 \
    && docker-php-ext-enable xdebug
