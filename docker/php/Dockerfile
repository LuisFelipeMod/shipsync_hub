FROM php:8.4-cli-bookworm

# install-php-extensions evita prompts interativos do PECL (memcached/zlib) no build.
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    && install-php-extensions zip pcntl sockets redis memcached \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
