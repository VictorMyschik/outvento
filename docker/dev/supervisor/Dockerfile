FROM php:8.5-cli-alpine

# --------------------------------------------------
# Environment
# --------------------------------------------------
ENV TZ=UTC

# --------------------------------------------------
# System dependencies
# --------------------------------------------------
RUN apk add --no-cache \
    bash \
    pkgconf \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    libxml2-dev \
    linux-headers \
    $PHPIZE_DEPS



# --------------------------------------------------
# PHP extensions (core)
# --------------------------------------------------
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    zip \
    mbstring

# --------------------------------------------------
# PHP extensions (PECL)
# --------------------------------------------------

# Redis
RUN pecl install redis \
 && docker-php-ext-enable redis

# --------------------------------------------------
# Composer (artisan нужен)
# --------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# PHP config
# --------------------------------------------------
COPY php.ini /usr/local/etc/php/php.ini
COPY jit.ini /usr/local/etc/php/conf.d/99-jit.ini


# --------------------------------------------------
# Supervisor config
# --------------------------------------------------
RUN apk add --no-cache supervisor

RUN mkdir -p /etc/config/conf.d

COPY config/conf.d/* /etc/supervisor/conf.d/
COPY config/supervisord.conf /etc/supervisor/

# --------------------------------------------------
# Workdir
# --------------------------------------------------
WORKDIR /var/www/travel

# --------------------------------------------------
# Run config
# --------------------------------------------------
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
