FROM php:8.5-fpm-alpine

ENV TZ=Europe/Warsaw

RUN apk add --no-cache \
    bash \
    git \
    curl \
    unzip \
    icu-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    postgresql-dev \
    linux-headers \
    $PHPIZE_DEPS

# PHP extensions (ТОЛЬКО то, что реально компилируется)
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    zip \
    sockets \
    mbstring

# runtime (НЕ удаляем!)
RUN apk add --no-cache \
    imagemagick \
    libwebp \
    libwebp-dev \
    libwebp-tools

# dev зависимости (можно удалить)
RUN apk add --no-cache --virtual .imagick-build-deps \
    imagemagick-dev \
    $PHPIZE_DEPS \
 && pecl install imagick \
 && docker-php-ext-enable imagick \
 && apk del .imagick-build-deps \
 && rm -rf /tmp/pear
    # Node.js + npm (for Vite / Laravel Mix)
RUN apk add --no-cache \
    nodejs \
    npm

RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
 && docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
 && docker-php-ext-install gd


# Xdebug (dev / qa)
ARG INSTALL_XDEBUG=true
RUN if [ "$INSTALL_XDEBUG" = "true" ]; then \
        pecl install xdebug && docker-php-ext-enable xdebug ; \
    fi

# Redis
RUN pecl install redis \
 && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP config
COPY php.ini /usr/local/etc/php/php.ini
COPY jit.ini /usr/local/etc/php/conf.d/99-jit.ini


WORKDIR /var/www/outvento

CMD ["php-fpm"]
