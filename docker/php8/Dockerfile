FROM php:8.1-fpm-alpine

#Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

CMD php-fpm

EXPOSE 9000
