#!/bin/bash

# Deployment script for Laravel on Render
# This script runs migrations and optimizations

set -e

echo "Starting Laravel deployment..."

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# Clear and cache configurations
php artisan config:clear
php artisan config:cache

php artisan route:clear
php artisan route:cache

php artisan view:clear
php artisan view:cache

# Run migrations
php artisan migrate --force

# Clear and cache events
php artisan event:clear
php artisan event:cache

# Optimize composer
composer dump-autoload --optimize

echo "Laravel deployment completed successfully!"
