FROM php:8.5-fpm-alpine

RUN apk add --no-cache curl git \
    build-base zlib-dev oniguruma-dev \
    autoconf bash libpq-dev \
    libsodium-dev libxml2-dev linux-headers
RUN apk add --no-cache argon2-dev rabbitmq-c-dev

RUN docker-php-ext-install pdo pdo_pgsql sodium

RUN pecl install redis amqp xdebug && docker-php-ext-enable redis amqp xdebug

RUN docker-php-ext-install -j$(nproc) bcmath

COPY ./dockerfiles/php/conf/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

ARG PUID=1000
ARG PGID=1000
RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

WORKDIR /var/www/app/
COPY . /var/www/app/

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
#RUN composer install --no-interaction

CMD php-fpm

EXPOSE 9000
