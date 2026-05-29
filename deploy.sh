#!/bin/bash
set -e

echo "=== Initializing Production Zero-Downtime Deployment ==="

# Go to backend root
cd "$(dirname "$0")/mamun-automobiles-backend"

# 1. Warm-up code dependencies & pull
echo "Pulling latest stable updates..."
git pull origin main || git pull origin backend || true

# 2. Install composer packages
echo "Installing Composer packages..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# 3. Compile client Vite production build
echo "Compiling Vite assets..."
cd ../frontend
npm install
npm run build
cd ../mamun-automobiles-backend

# 4. Graceful Horizon queue draining to prevent active job interruptions
echo "Draining active queue workers gracefully..."
php artisan horizon:terminate || php artisan queue:restart || true

# 5. Run rollback-safe migrations
echo "Executing database migrations..."
php artisan migrate --force

# 6. Cache warming
echo "Caching configurations and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart Horizon processes
echo "Reloading Supervisor configurations..."
sudo supervisorctl reread || true
sudo supervisorctl update || true
sudo supervisorctl restart all || true

echo "=== Deployment Successfully Completed ==="
