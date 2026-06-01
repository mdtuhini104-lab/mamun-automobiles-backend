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
    gettext \
    supervisor

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

# Final Build Sequence
RUN composer install --no-dev
RUN composer dump-autoload --optimize
RUN php artisan optimize

# Runner target containing clean production build
FROM base AS runner
WORKDIR /var/www/html
COPY --from=developer /var/www/html .

# Setup Nginx, Supervisor & Start Script
COPY nginx-railway.conf /etc/nginx/nginx.conf.template
COPY supervisord.conf /etc/supervisord.conf
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Force PHP-FPM to use UNIX socket and not clear env vars
RUN echo "listen = /var/run/php-fpm.sock" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "listen.owner = root" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "listen.group = root" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "listen.mode = 0666" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "clear_env = no" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "catch_workers_output = yes" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Remove default nginx config if exists
RUN rm -f /etc/nginx/conf.d/default.conf

# Ensure storage permissions during build
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Start script will handle envsubst and start supervisord
CMD ["/usr/local/bin/start.sh"]
