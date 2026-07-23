# Деплой Laravel (api/) на shared-хостинг Beget

Домен: `gbsale.ru`
Хостинг: Beget shared, nginx, PHP 8.4.6/8.5.2 (в панели выбран PHP 8.5, фактически работает 8.4.6)
База данных: облачная PostgreSQL (`oritharuskog.beget.app:5432`)

## Что развёрнуто

- Laravel 11 + Filament (`api/`)
- Filament-админка (`/admin`)
- Публичный сайт на Blade + Alpine.js (`/`)
- API для 1С (`/api/1c/*`)
- API бота первой линии (`/api/bot/*`)

Парольная заглушка `111` действует на все публичные web-страницы, кроме `/admin*`, `/api/*`, `/up` и статических assets (`/css/*`, `/js/*`, `/images/*`, `/favicon.ico`).

## 1. Подготовка в панели Beget

1. Домен `gbsale.ru` привязан к директории `public_html`.
2. Включен PHP 8.4+ для сайта.
3. В облачной БД создана база `default_db`, пользователь `cloud_user` имеет доступ.

## 2. Загрузка кода на сервер

### Вариант A. Через SSH + git

```bash
ssh mastak97@<beget-ssh-host>
cd ~
git clone <repo-url> gb-site
cd gb-site/api
composer install --no-dev --optimize-autoloader --no-interaction
```

### Вариант B. Без SSH (FTP + PHP-деплойер)

1. Загрузите содержимое `api/` по FTP в `~/gb-site/api/`.
2. Скопируйте `deploy/beget/deploy-no-ssh.php`, замените `DEPLOY_TOKEN` на сильный токен и загрузите в `~/gb-site/api/public/deploy-no-ssh.php`.
3. Откройте `https://gbsale.ru/deploy-no-ssh.php?token=YOUR_TOKEN`.
4. Скрипт установит зависимости, сгенерирует `APP_KEY`, применит миграции, создаст кэши и перестроит индекс бота.
5. **Удалите `deploy-no-ssh.php` из `public/` сразу после деплоя.**

## 3. Настройка `public_html`

Рекомендуется заменить `public_html` симлинком на `~/gb-site/api/public`:

```bash
cd ~
rm -rf public_html
ln -s ~/gb-site/api/public public_html
```

Если Beget не позволяет использовать симлинк, скопируйте содержимое `api/public/` в `public_html` и поправьте пути в `public_html/index.php`:

```php
require __DIR__.'/../gb-site/api/vendor/autoload.php';
// ...
(require_once __DIR__.'/../gb-site/api/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

## 4. Настройка `.env`

```bash
cd ~/gb-site/api
cp deploy/beget/.env.beget.example .env
# отредактируйте .env
php artisan key:generate
```

Обязательно проверить:
- `APP_KEY=base64:...`
- `APP_URL=https://gbsale.ru`
- `DB_HOST=oritharuskog.beget.app`, `DB_PORT=5432`, `DB_DATABASE=default_db`, `DB_USERNAME=cloud_user`, `DB_PASSWORD=...`
- `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`
- `IMPORT_1C_API_KEY` и `BOT_API_KEY` — задать сильные ключи (сейчас временные `test-1c-key` / `test-bot-key`)

## 5. Миграции и кэши

```bash
cd ~/gb-site/api
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan bot:rebuild-index
```

## 6. Cron

В панели Beget добавить задачу каждую минуту:

```bash
/usr/bin/php /home/m/mastak97/gbsale.ru/api/artisan schedule:run >> /home/m/mastak97/gbsale.ru/api/storage/logs/scheduler.log 2>&1
```

`schedule:run` запустит:
- `bot:rebuild-index` раз в час
- `1c:process-queue` каждую минуту
- `queue:work --stop-when-empty` каждую минуту

## 7. Права на папки

```bash
cd ~/gb-site/api
chmod -R 755 storage bootstrap/cache
find storage -type d -exec chmod 775 {} +
find storage -type f -exec chmod 664 {} +
```

## 8. Подключение к базе данных

Credentials лежат в `deploy/beget/.env.local` (не в git).

Параметры:
- **Host:** `oritharuskog.beget.app`
- **Port:** `5432`
- **Database:** `default_db`
- **User:** `cloud_user`
- **Password:** см. `.env.local`

### Через Adminer (браузер)

1. Загрузите `deploy/beget/adminer.php` на сервер в `api/public/adminer.php`.
2. Откройте `https://gbsale.ru/adminer.php`.
3. Система: `PostgreSQL`, сервер: `oritharuskog.beget.app:5432`, логин/пароль из `.env.local`.
4. **Удалите `adminer.php` из `public/` после использования.**

### Через DBeaver / pgAdmin

- Создайте подключение PostgreSQL с параметрами выше.
- Облачная БД Beget доступна только с IP сервера, поэтому для локального подключения используйте SSH-туннель через `mastak97_gbsale@<beget-ssh-host>`.

### Через Laravel Tinker (на сервере)

```bash
cd ~/gb-site/api
php artisan tinker
>>> \App\Models\Product::count();
>>> \App\Models\BotProduct::limit(10)->get();
```

## 9. Импорт файловой выгрузки CommerceML без SSH

1. Убедитесь, что папка `docs/ВыгрузкаДляБота/` загружена на сервер.
2. Скопируйте `deploy/beget/run-import-no-ssh.php` в `api/public/`, замените `RUN_IMPORT_TOKEN` на сильный токен.
3. Откройте в браузере:
   ```
   https://gbsale.ru/run-import-no-ssh.php?token=YOUR_TOKEN
   ```
4. Скрипт выполнит:
   - `php artisan 1c:import-commerceml ... --apply --chunk=500`
   - `php artisan queue:work --stop-when-empty`
   - `php artisan bot:rebuild-index`
5. **Удалите `run-import-no-ssh.php` из `public/` сразу после импорта.**

## 10. Проверка

- Главная: `https://gbsale.ru` (пароль `111`)
- Админка: `https://gbsale.ru/admin`
- API 1С: `GET https://gbsale.ru/api/1c/products/{uuid_1c}` с `X-1C-API-Key: <ключ>`
- API бота: `POST https://gbsale.ru/api/bot/products/search` с `X-Bot-API-Key: <ключ>`

## 11. Ограничения

- Нет постоянного `queue:work` — очереди обрабатываются пачками по cron.
- Нет Redis — используем `database` для cache, queue, session.
- Для больших загрузок картинок из 1С лучше подключить S3.

## 12. Решение проблем

```bash
tail -n 50 ~/gb-site/api/storage/logs/laravel.log
```
