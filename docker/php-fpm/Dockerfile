FROM composer:latest AS composer
FROM php:7.2-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev

RUN apt-get install git -y

# Install mysqli PDO
RUN apt-get install -y libpq-dev \
    && docker-php-ext-install mysqli pdo_mysql

RUN docker-php-ext-install zip

CMD ["php-fpm"]
