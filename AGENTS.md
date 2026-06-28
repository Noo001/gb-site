# AGENTS.md — Gadget Bar

## Где и как развёрнуто

- **Сервер**: Windows 10 Pro + WSL2 Ubuntu-22.04, белый IP `5.3.164.6`.
- **Домен**: `gb.5.3.164.6.sslip.io` (управляется в `../proxy/.env`).
- **Прокси**: Caddy 2 в Docker (`../proxy/`), терминирует TLS и маршрутизирует запросы.
- **Бэкенд**: Laravel 11 + Filament (`api/`).
- **Фронтенд**: Next.js 16 (`frontend/`).
- **БД**: PostgreSQL 16 (`gb-db`).
- **Adminer**: https://gb.5.3.164.6.sslip.io/adminer/

## Запуск production-стека

```bash
cd /mnt/c/up/server/projects/gb-site
docker compose -f docker-compose.caddy.yml --env-file .env.prod up -d --build
```

## Автодеплой

- **Сервис**: `gb-site-deploy-webhook.service` в WSL.
- **Скрипт**: `/opt/gb-site-deploy/deploy-webhook.py`.
- **Лог**: `/var/log/gb-site-deploy.log`.
- **Статус**: https://gb.5.3.164.6.sslip.io/deploy-status

Каждые 60 секунд сервис проверяет `main` ветку репозитория. При новом коммите:

1. `git stash -u`
2. `git pull origin main`
3. `git stash pop`
4. Пересобирает только изменённые сервисы:
   - `docker/php/*` или `docker/prod/api-entrypoint.sh` → `api`
   - `frontend/*` или `docker/prod/frontend.Dockerfile` → `frontend`

> Важно: исполняемый файл сервиса лежит в `/opt/gb-site-deploy/deploy-webhook.py` (вне репозитория). После изменений в `deploy/docker/prod/deploy-webhook.py` скопируй файл на сервер и перезапусти сервис:
> ```bash
> sudo cp deploy/docker/prod/deploy-webhook.py /opt/gb-site-deploy/deploy-webhook.py
> sudo systemctl restart gb-site-deploy-webhook.service
> ```

## Правила работы

- **Не редактируй deployment-конфиги только на сервере**. Актуальные конфиги лежат в `deploy/`.
- **Всегда коммить и пушь** изменения в `main`. Иначе автодеплой их перезатрёт при следующем `git pull`.
- `.env.prod` содержит секреты и **не пушится**. Если добавляешь новые env-переменные, уточни у пользователя, нужно ли их добавить в `.env.prod` на сервере.
- Если меняешь домен, обнови:
  - `../proxy/.env`
  - `../proxy/Caddyfile` (использует `{$DOMAIN}`)
  - `.env.prod` на сервере

## Доступы

- **Админка**: https://gb.5.3.164.6.sslip.io/admin
  - Логин: `admin@gb.5.3.164.6.sslip.io`
  - Пароль: *см. `.env.prod` на сервере (`ADMIN_PASSWORD`)*
- **БД через Adminer**:
  - Сервер: `db`
  - Пользователь: *см. `.env.prod` (`DB_USERNAME`)*
  - Пароль: *см. `.env.prod` (`DB_PASSWORD`)*
  - База: *см. `.env.prod` (`DB_DATABASE`)*

## Обязательные env-переменные (добавить в `.env.prod` на сервере)

```env
# 1C
IMPORT_1C_API_KEY=<сильный-ключ>
EXPORT_1C_WEBHOOK_URL=<URL вебхука в 1С>

# Бот первой линии
BOT_API_KEY=<сильный-ключ>
```

## Команды после деплоя

После обновления образа `gb-api`:

```bash
docker exec gb-api php artisan migrate --force
docker exec gb-api php artisan bot:rebuild-index
```

Перестройка индекса также запускается автоматически каждый час — для этого в `docker-compose.caddy.yml` добавлен сервис `gb-scheduler`, который выполняет `php artisan schedule:work`.

## Файлы деплоя в репозитории

```text
deploy/
├── docker-compose.caddy.yml
├── docker/prod/deploy-webhook.py
├── proxy/Caddyfile
├── proxy/docker-compose.yml
├── scripts/ramo-docker-start.sh
└── scripts/ramo-docker.service
```

Это копии серверных конфигов. При изменениях сначала правь файлы в `deploy/`, потом копируй их на сервер и/или делай симлинки.
