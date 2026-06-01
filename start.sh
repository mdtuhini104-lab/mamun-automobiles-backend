#!/bin/sh
# Replace PORT variable in nginx config
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Start php-fpm in background
php-fpm -D

# Start nginx in foreground
exec nginx -g 'daemon off;'
