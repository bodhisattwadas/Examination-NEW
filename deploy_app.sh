#!/usr/bin/env bash
# ==============================================================================
# Examination Duty Portal - Application Deployment & Optimization Script
# ==============================================================================
set -e

APP_DIR="/var/www/examination-portal"
cd $APP_DIR

echo ">>> Setting correct file permissions..."
sudo chown -R $USER:www-data $APP_DIR

echo ">>> Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

echo ">>> Running database migrations..."
php artisan migrate --force

echo ">>> Clearing & caching configuration for maximum performance..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">>> Ensuring storage & cache permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo ">>> Reloading PHP-FPM and Nginx..."
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx

echo ">>> Portal successfully deployed & optimized!"
