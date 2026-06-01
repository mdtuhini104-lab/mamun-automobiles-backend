#!/bin/sh
# Replace PORT variable in nginx config
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Ensure storage permissions are set for php-fpm (which runs as www-data)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Start supervisor to keep both nginx and php-fpm alive in foreground
exec /usr/bin/supervisord -c /etc/supervisord.conf
