# Деплой

Production развёрнут на **Beget shared hosting** (`gbsale.ru`).
Локальный Docker-стек больше не используется — сервер закрыт.

## Production (Beget)

Деплой выполняется скриптом `deploy_remote.py` из корня репозитория:

```bash
python deploy_remote.py
```

Скрипт:
1. Подключается по SSH к `gbsale.ru`.
2. Делает `git fetch origin && git reset --hard origin/main`.
3. Запускает `deploy/beget/deploy.sh`, который:
   - устанавливает PHP-зависимости (`composer install --no-dev`);
   - синхронизирует статику из `api/public/` в `public_html/`;
   - накатывает миграции;
   - обновляет кэши config/route/view;
   - пересоздаёт индекс бота.

После деплоя вручную:

```bash
cd /home/m/mastak97/gbsale.ru/api
/usr/local/bin/php8.4 artisan migrate --force
/usr/local/bin/php8.4 artisan bot:rebuild-index
```

## Важно про статику

`public_html` — это **копия** `api/public/`, а не симлинк. После изменений в `api/public/css`, `api/public/js`, `api/public/images` или `favicon*` обязательно запускать деплой, чтобы файлы скопировались в `public_html`.

## Локальная разработка (Docker)

Если нужен локальный стенд:

```bash
cp .env.example .env
# отредактировать подключение к БД, APP_KEY и т.д.

docker compose up -d
docker compose exec api composer install
docker compose exec api php artisan migrate
```

- Сайт: http://localhost:8000
- API: http://localhost:8000/api
- Admin: http://localhost:8000/admin

## Полезные команды

```bash
# Загрузить картинки товаров с gadget-bar.ru
php artisan import:images --type=products --delay=500 --skip-existing=1

# Загрузить картинки категорий / логотипы брендов
php artisan import:images --type=categories --delay=300 --skip-existing=1

# Пересоздать индекс бота
php artisan bot:rebuild-index

# Сверка с 1С
php artisan reconcile:1c --deactivate-missing --cleanup-logs
```
