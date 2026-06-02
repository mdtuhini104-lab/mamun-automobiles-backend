#!/bin/sh
# Replace PORT variable in nginx config
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Ensure storage permissions are completely open to prevent any permission issues
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Clear any cached Laravel config/routes to ensure environment variables are read dynamically at runtime
php artisan optimize:clear || true

# Run a background diagnostic check after 10 seconds to print active processes and open ports to logs
(
    sleep 10
    echo "=== RUNTIME DIAGNOSTIC CHECK ==="
    echo "Active Processes:"
    ps aux
    echo "Open Ports (netstat):"
    netstat -tuln
    echo "Checking FPM port 9000 response:"
    nc -zv 127.0.0.1 9000 || echo "Port 9000 is CLOSED"
    echo "Checking Nginx process config:"
    nginx -t
    echo "=== END DIAGNOSTIC CHECK ==="
) &

# Start supervisor to keep both nginx and php-fpm alive in foreground
exec /usr/bin/supervisord -c /etc/supervisord.conf
