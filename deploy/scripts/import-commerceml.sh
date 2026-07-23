#!/usr/bin/env bash
set -euo pipefail

# Импорт файловой выгрузки CommerceML 3.1 из 1С на shared-хостинге Beget.
# Путь к api/ и папке выгрузки можно переопределить через переменные окружения.

API_DIR="${API_DIR:-$HOME/gbsale.ru/api}"
EXPORT_DIR="${EXPORT_DIR:-$HOME/gbsale.ru/docs/ВыгрузкаДляБота}"
CHUNK="${CHUNK:-500}"

if [ ! -d "$API_DIR" ]; then
    echo "ERROR: API directory not found: $API_DIR" >&2
    exit 1
fi

if [ ! -d "$EXPORT_DIR" ]; then
    echo "ERROR: CommerceML export directory not found: $EXPORT_DIR" >&2
    exit 1
fi

cd "$API_DIR"

echo "Starting CommerceML import from $EXPORT_DIR"
php artisan 1c:import-commerceml "$EXPORT_DIR" --apply --chunk="$CHUNK"

echo "Import batch queued. Run 'php artisan queue:work' or wait for cron schedule:run."
