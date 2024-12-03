FROM composer:2 AS composer
FROM php:8.3-cli AS php

RUN apt-get update && \
    apt-get -y upgrade && \
    apt-get install -y --no-install-recommends git && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN mkdir -p /app
WORKDIR /app

COPY composer.json composer.json
COPY composer.lock composer.lock
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN composer install \
    --no-dev \
    --quiet \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist && \
    composer dump-autoload && \
    composer clear-cache

WORKDIR /app
COPY scripts/index.php public/index.php
COPY src/ src

CMD ["php", "public/index.php"]

