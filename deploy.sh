#!/bin/bash
set -e

echo "Deploying Mamun ERP..."

# Enter maintenance mode
cd mamun-automobiles-backend
php artisan down || true

# Update codebase
git pull origin backend

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm install --prefix ../frontend

# Build frontend
npm run build --prefix ../frontend

# Migrate database
php artisan migrate --force

# Optimize caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queues
php artisan queue:restart

# Restart Docker containers (Zero Downtime attempt using docker-compose up -d --build)
cd ..
docker-compose up -d --build

# Exit maintenance mode
cd mamun-automobiles-backend
php artisan up

echo "Deployment finished successfully!"
