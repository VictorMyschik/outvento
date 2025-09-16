FROM php:8.4-fpm

# Install dependencies in a single RUN to reduce layers and improve caching
RUN apt-get update && apt-get install -y --no-install-recommends --no-install-suggests \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        librabbitmq-dev \
        build-essential \
        openssh-server \
        nodejs \
        default-mysql-client \
        npm \
        bc \
        unzip \
        libbz2-dev \
        libfreetype6-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        postgresql-client \
        mc \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        pdo \
        pdo_pgsql \
        pgsql \
        bz2 \
        gd \
        exif \
        opcache \
        zip \
        sockets

# Install PECL extensions
RUN pecl install -o -f redis xmlrpc-1.0.0RC3 amqp xdebug \
    && docker-php-ext-enable redis xmlrpc amqp xdebug \
    && rm -rf /tmp/pear

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --quiet --install-dir=/usr/local/bin --filename=composer

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && npm install -g npm@11.1.0 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Clean up PHP source
RUN docker-php-source delete

WORKDIR /var/www/travel