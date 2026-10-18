
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mysqli pdo_mysql \
    && a2enmod rewrite ssl env

COPY docker/apache-ssl.conf /etc/apache2/sites-available/000-default-ssl.conf

RUN a2ensite 000-default-ssl.conf

WORKDIR /var/www/html
