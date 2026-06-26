#!/bin/sh
set -e

cd /var/www/api

# Install dependencies if vendor is missing (first run / clean volume)
if [ ! -f vendor/autoload.php ]; then
    composer install --no-dev --no-interaction --optimize-autoloader
fi

# Generate app key if not provided
if [ -z "$APP_KEY" ]; then
    APP_KEY=$(php artisan key:generate --show)
    export APP_KEY
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan filament:assets --no-interaction || true

php artisan migrate --force

exec php-fpm -R
