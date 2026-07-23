#!/bin/sh
set -e

cd /var/www/api

# Fallback to .env.testing for non-production contours where .env.prod
# does not define 1C/bot keys. Docker Compose env vars still take precedence.
if [ -f .env.testing ] && [ -z "$IMPORT_1C_API_KEY" ]; then
    cp .env.testing .env
fi

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

# Rebuild bot product index after deploy
php artisan bot:rebuild-index || true

exec php-fpm -R
