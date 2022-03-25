ARG PHP_VERSION=7
FROM php:${PHP_VERSION}-fpm-alpine AS php

RUN apk update && apk upgrade

ADD ./php/www.conf /usr/local/etc/php-fpm.d/www.conf

ENV PHPGROUP=genie
RUN addgroup -g 1000 ${PHPGROUP}
ENV PHPUSER=genie
RUN adduser -G ${PHPGROUP} -g ${PHPGROUP} -s /bin/sh -D ${PHPUSER}

RUN mkdir -p /var/www/html

ADD ../ /var/www/html

RUN docker-php-ext-install \
    pdo \
    pdo_mysql

RUN apk add g++ autoconf
RUN apk add lua5.1 lua5.1-dev

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync

RUN install-php-extensions yaf yac
RUN install-php-extensions luasandbox lua

RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS} && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    apk del pcre-dev ${PHPIZE_DEPS}

RUN chown -R ${PHPUSER}:${PHPGROUP} .

FROM php AS php_dev

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache bash curl vim

RUN apk add --no-cache -f git composer nodejs npm yarn

RUN npm install -g npm@latest --force
RUN npm install -g yarn@latest --force
