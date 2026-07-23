#!/usr/bin/env bash
# Deploy Laravel backend to Beget shared hosting
# Run this script on the server via SSH.

set -e

PROJECT_DIR="${PROJECT_DIR:-$HOME/gb-site}"
API_DIR="$PROJECT_DIR/api"
PHP_BIN="${PHP_BIN:-$(command -v php || echo /usr/local/bin/php)}"
COMPOSER_BIN="${COMPOSER_BIN:-$(command -v composer || echo $HOME/composer)}"

echo "==> PHP version"
$PHP_BIN -v

echo "==> Installing dependencies"
cd "$API_DIR"
$COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction

echo "==> Setting up .env"
if [ ! -f "$API_DIR/.env" ]; then
    cp "$API_DIR/deploy/beget/.env.beget.example" "$API_DIR/.env"
    echo "!!! Created .env from example. Edit it and set strong API keys and APP_KEY before continuing."
fi

# Generate APP_KEY if empty
if ! grep -qE '^APP_KEY=base64:' "$API_DIR/.env"; then
    $PHP_BIN artisan key:generate --force
fi

echo "==> Running migrations"
$PHP_BIN artisan migrate --force

echo "==> Storage link"
$PHP_BIN artisan storage:link || true

echo "==> Caches"
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

echo "==> Rebuilding bot index"
$PHP_BIN artisan bot:rebuild-index

echo "==> Fixing permissions"
chmod -R 755 "$API_DIR/storage" "$API_DIR/bootstrap/cache"
find "$API_DIR/storage" -type d -exec chmod 775 {} \;
find "$API_DIR/storage" -type f -exec chmod 664 {} \;

echo "==> Done. Add cron job in Beget panel:"
echo "    * * * * * $PHP_BIN $API_DIR/artisan schedule:run >> $API_DIR/storage/logs/scheduler.log 2>&1"
