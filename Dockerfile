# Multi-stage production packaging
FROM php:8.3-fpm-alpine AS base

# Install extensions & web server requirements
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    nginx \
    gettext

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

# Copy Laravel backend source files BEFORE composer install
COPY mamun-automobiles-backend/ .

# Temporary debug verification
RUN pwd
RUN ls -la
RUN ls -la /var/www/html

# Final Build Sequence
RUN composer install --no-dev
RUN composer dump-autoload --optimize
RUN php artisan optimize

# Runner target containing clean production build
FROM base AS runner
WORKDIR /var/www/html
COPY --from=developer /var/www/html .

# Setup Nginx & Start Script
COPY nginx-railway.conf /etc/nginx/nginx.conf.template
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Ensure storage permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Remove default nginx config if exists
RUN rm -f /etc/nginx/conf.d/default.conf

# Start script will handle nginx and php-fpm
CMD ["/usr/local/bin/start.sh"]
