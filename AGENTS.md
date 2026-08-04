# AGENTS.md — Gadget Bar

> **Терминология:** проект разрабатывается как **Единая база** — центральное хранилище данных для 1С, бота первой линии и других систем. Веб-интерфейс на `gbsale.ru` является одной из точек доступа к этой базе, но приоритетной ролью является именно единая база данных. В документации ниже слово "сайт" сохранено там, где речь идёт непосредственно о веб-интерфейсе.

## Где и как развёрнуто

- **Сервер**: Windows 10 Pro + WSL2 Ubuntu-22.04, белый IP `5.3.164.6`.
- **Домен**: `gbsale.ru` (управляется в `../proxy/.env`).
- **Прокси**: Caddy 2 в Docker (`../proxy/`), терминирует TLS и маршрутизирует запросы.
- **Бэкенд**: Laravel 11 + Filament (`api/`), включая публичный сайт на Blade.
- **БД**: облачная PostgreSQL Beget (`oritharuskog.beget.app:5432`).
- **Adminer**: через панель управления Beget (в разделе PostgreSQL), не через сайт.

## Запуск production-стека

Production-стек сейчас развёрнут на **Beget shared hosting** (`gbsale.ru`). Локальный Docker-стек (WSL) больше не используется — сервер был закрыт.

Деплой выполняется через `deploy/beget/deploy.sh` на сервере по SSH.

## Автодеплой

Автодеплой на WSL-сервере ранее был настроен, но сервер закрыт. Сейчас деплой выполняется вручную через `deploy/beget/deploy.sh` на сервере Beget.

Старые инструкции по WSL-деплою оставлены ниже для истории:

<!--
- **Сервис**: `gb-site-deploy-webhook.service` в WSL.
- **Скрипт**: `/opt/gb-site-deploy/deploy-webhook.py`.
- **Лог**: `/var/log/gb-site-deploy.log`.
- **Статус**: https://gbsale.ru/deploy-status

Каждые 60 секунд сервис проверяет `main` ветку репозитория. При новом коммите:
1. `git stash -u`
2. `git pull origin main`
3. `git stash pop`
4. Пересобирает изменения в `api/`.

> Важно: исполняемый файл сервиса лежит в `/opt/gb-site-deploy/deploy-webhook.py` (вне репозитория). После изменений в `deploy/docker/prod/deploy-webhook.py` скопируй файл на сервер и перезапусти сервис:
> ```bash
> sudo cp deploy/docker/prod/deploy-webhook.py /opt/gb-site-deploy/deploy-webhook.py
> sudo systemctl restart gb-site-deploy-webhook.service
> ```
-->

## Где сейчас лежат секреты (временно)

> ⚠️ Пользователь попросил сохранить секреты в репозитории на этапе запуска, чтобы агент не терял их между сессиями. После выхода в прод этот файл нужно удалить из Git и перенести секреты в безопасное хранилище.

- **Файл**: `.env.secrets` (в корне репозитория)
- **Что внутри**:
  - SSH/FTP доступ к Beget (`gbsale.ru`)
  - Параметры PostgreSQL (Beget cloud)
  - Laravel `APP_KEY`
  - `IMPORT_1C_API_KEY` и `BOT_API_KEY`
  - Плейсхолдеры для `EXPORT_1C_WEBHOOK_URL` и `ADMIN_PASSWORD`
- **Копия на сервере**: `/home/m/mastak97/gbsale.ru/api/.env`

## Правила работы

- **Не редактируй deployment-конфиги только на сервере**. Актуальные конфиги лежат в `deploy/`.
- **Всегда коммить и пушь** изменения в `main`. Иначе автодеплой их перезатрёт при следующем `git pull`.
- Актуальный прод-конфиг — `deploy/beget/.env.beget` (локально, в `.gitignore`, **не коммитится** — содержит секреты), он копируется в `api/.env` на сервере. Шаблон без секретов — `deploy/beget/.env.beget.example`. Если добавляешь новые env-переменные, уточни у пользователя, нужно ли их добавить туда и на сервер.
- **`.env.testing`** лежит в репозитории и содержит тестовые ключи для локального тестирования и тестового стенда. Перед продом эти ключи нужно заменить/убрать.
- **`.env.secrets`** — временный файл с реальными секретами. Удалить из Git после запуска.
- Если меняешь домен, обнови:
  - `../proxy/.env`
  - `../proxy/Caddyfile` (использует `{$DOMAIN}`)
  - `deploy/beget/.env.beget` и `api/.env` на сервере

## Доступы

- **Админка**: https://gbsale.ru/admin
  - Логин: `admin@gbsale.ru`
  - Пароль: *см. `.env` на сервере (`ADMIN_PASSWORD`)*
- **БД через Adminer**:
  - Открыть через панель управления Beget → раздел PostgreSQL.
  - Параметры подключения:
    - Хост: `oritharuskog.beget.app:5432`
    - База: `default_db`
    - Пользователь: `cloud_user`
    - Пароль: *см. `.env` на сервере (`DB_PASSWORD`)*
- **Deploy hook** (если используется): basic-auth на уровне Caddy — `{$DEPLOY_AUTH_USER}` / пароль из `{$DEPLOY_AUTH_PASSWORD_HASH}`
  - Хэш пароля — bcrypt, генерируется командой:
    ```bash
    docker run --rm caddy:2-alpine caddy hash-password --plaintext 'YOUR_PASSWORD'
    ```

## Обязательные env-переменные (добавить в `api/.env` на сервере и в `deploy/beget/.env.beget`)

```env
# 1C
IMPORT_1C_API_KEY=<сильный-ключ>
EXPORT_1C_WEBHOOK_URL=<URL вебхука в 1С>

# Бот первой линии
BOT_API_KEY=<сильный-ключ>

# Basic auth на уровне Caddy (значения — примеры, задать на сервере)
ADMINER_AUTH_USER=admin
ADMINER_AUTH_PASSWORD_HASH=<bcrypt-хэш>
DEPLOY_AUTH_USER=deploy
DEPLOY_AUTH_PASSWORD_HASH=<bcrypt-хэш>
```

## Команды после деплоя

После деплоя на Beget:

```bash
cd /home/m/mastak97/gbsale.ru/api
/usr/local/bin/php8.4 artisan migrate --force
/usr/local/bin/php8.4 artisan bot:rebuild-index
```

Перестройка индекса также запускается через cron `schedule:run` каждую минуту.

## Деплой на Beget (gbsale.ru)

Проект развёрнут на shared-хостинге Beget как **Laravel-приложение** (`api/`). Веб-интерфейс отдаётся шаблонами Blade из `api/resources/views/`.

- **Домен**: `gbsale.ru`
- **Аккаунт**: `mastak97`
- **Веб-сервер**: Apache, DocumentRoot = `public_html`
- **PHP**: 8.4
- **БД**: облачная PostgreSQL `oritharuskog.beget.app:5432`
  - База: `default_db`
  - Пользователь: `cloud_user`
  - Пароль: *задать в `.env` на сервере, не пушить*
- **Queue/Cron**: `schedule:run` каждую минуту, постоянный `queue:work` невозможен — очереди обрабатываются пачками (`queue:work --stop-when-empty`) через тот же cron.

### Файлы и инструкции

- `deploy/beget/README.md` — пошаговый мануал деплоя.
- `deploy/beget/.env.beget.example` — шаблон `.env` для сервера (секреты не пушить).
- `deploy/beget/deploy.sh` — скрипт деплоя, запускается на сервере по SSH.
- `deploy/beget/public_html/.htaccess` — Apache rewrite rules для Laravel.

### Особенности

- `public_html` должен указывать на `api/public` (симлинк) или содержать копию `public/` с поправленными путями в `index.php`.
- Redis недоступен — используем `database` для cache, queue, session.
- Для картинок из 1С в перспективе подключить S3 (Beget объектное хранилище).

## Файлы деплоя в репозитории

```text
deploy/
├── beget/
│   ├── README.md
│   ├── .env.beget.example
│   ├── deploy.sh
│   └── public_html/
│       └── .htaccess
├── docker-compose.caddy.yml
├── docker/prod/deploy-webhook.py
├── proxy/Caddyfile
├── proxy/docker-compose.yml
├── scripts/ramo-docker-start.sh
└── scripts/ramo-docker.service
```

Это копии серверных конфигов. При изменениях сначала правь файлы в `deploy/`, потом копируй их на сервер и/или делай симлинки.

## Отложенные задачи

### Безопасность бота первой линии

Реализовать позже, после запуска на тестовом стенде и стабилизации обмена с 1С:

- Изолированная среда БД для бота: отдельный readonly DB-юзер `bot_reader` с `SELECT` только на `bot_products`, `bot_knowledge`, `bot_trade_in_prices`, `stores`.
- Rate limiting для `/api/bot/*` (30 req/min с IP, 100 req/min по API-ключу).
- IP whitelist для доверенных источников (n8n/Bitrix24/Telegram-шлюз).
- HMAC-подпись запросов (`X-Bot-Signature` + `X-Bot-Timestamp`), требует изменений в n8n-воркфлоу `docs/GadgetBar_Bitrix24_v3.json`.
- Лимит выдачи `/api/bot/products/search` до 20 результатов.
- Усиленное логирование и алерты на аномалии.

> Пока бот работает через `X-Bot-API-Key` без HMAC и без DB-изоляции.
