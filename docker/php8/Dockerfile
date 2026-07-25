FROM php:8.4-fpm-alpine

RUN apk --update --no-cache add bash git autoconf gcc make g++ \
        && pecl install pcov \
        && docker-php-ext-enable pcov

#Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

CMD ["php-fpm"]
