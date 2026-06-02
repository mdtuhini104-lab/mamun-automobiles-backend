#!/bin/sh
# Generate Nginx configuration dynamically, ensuring Nginx listens on port 9000 (Railway's detected port)
# and also on the custom $PORT (if set to something else like 8080) to handle both routing targets.
if [ -n "$PORT" ] && [ "$PORT" != "9000" ]; then
    echo "PORT is set to $PORT. Configuring Nginx to listen on $PORT and 9000."
    sed "s/listen \${PORT};/listen $PORT;\n        listen 9000;/g" /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf
else
    echo "PORT is not set or is 9000. Configuring Nginx to listen on 9000."
    sed "s/listen \${PORT};/listen 9000;/g" /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf
fi

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
    echo "Checking Nginx process config:"
    nginx -t
    echo "=== END DIAGNOSTIC CHECK ==="
) &

# Start supervisor to keep both nginx and php-fpm alive in foreground
exec /usr/bin/supervisord -c /etc/supervisord.conf
