FROM php:8.0-fpm

RUN docker-php-ext-install pdo pdo_mysql

RUN apt-get update && apt-get install -y libpng-dev
RUN apt-get install -y \
  libwebp-dev \
  libjpeg62-turbo-dev \
  libpng-dev libxpm-dev \
  libfreetype6-dev
RUN docker-php-ext-configure gd --with-jpeg --with-freetype
RUN docker-php-ext-install gd

RUN pecl install xdebug-3.2.0 \
  && docker-php-ext-enable xdebug

RUN docker-php-ext-install opcache
