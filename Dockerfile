
FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo_mysql \
    && a2enmod rewrite ssl

COPY docker/apache-ssl.conf /etc/apache2/sites-available/000-default-ssl.conf

RUN a2ensite 000-default-ssl.conf

WORKDIR /var/www/html
