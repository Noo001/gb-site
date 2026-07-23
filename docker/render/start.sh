#!/bin/sh
set -e

export PORT=${PORT:-10000}

# Generate nginx config from template
envsubst '$PORT' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Laravel runtime setup
cd /var/www/api

# Generate app key if not provided
if [ -z "$APP_KEY" ]; then
    APP_KEY=$(php artisan key:generate --show)
    export APP_KEY
fi

# Cache config/routes/views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Publish Filament assets (idempotent)
php artisan filament:assets --no-interaction || true

# Run database migrations
php artisan migrate --force

# Start all services via supervisord
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
