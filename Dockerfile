# Multi-stage production packaging
FROM php:8.3-fpm-alpine AS base

# Install extensions
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql pdo_pgsql zip mbstring bcmath gd pcntl

# Redis driver setup
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Stage for development/testing dependency caching
FROM base AS developer
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Runner target containing clean production build
FROM base AS runner
COPY --from=developer /var/www/html/vendor /var/www/html/vendor
COPY . .
RUN composer dump-autoload --optimize

EXPOSE 9000
CMD ["php-fpm"]
