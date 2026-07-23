# Деплой на локальный сервер

Архитектура: Docker Compose + Traefik (shared reverse proxy) + PostgreSQL + Redis + Meilisearch + MinIO.
Traefik позволяет запускать несколько сайтов на одном сервере по доменам без конфликтов портов.

## Подготовка

1. Установить Docker + Docker Compose.
2. Создать общую сеть (один раз на сервер):
   ```bash
   docker network create traefik-public
   ```
3. Скопировать и заполнить `.env.prod`:
   ```bash
   cp .env.prod .env.prod.local
   # отредактировать DOMAIN, пароли, ключи
   ```
4. Прописать домены в `/etc/hosts` (для локального теста):
   ```
   127.0.0.1  gadget-bar.local s3.gadget-bar.local console.gadget-bar.local
   ```

## Запуск

```bash
./deploy.sh
```

Или вручную:

```bash
# Общий Traefik (один раз на сервер)
docker compose -f docker-compose.traefik.yml up -d

# Этот проект
docker compose -f docker-compose.prod.yml --env-file .env.prod.local up -d
```

После запуска:

- Сайт: http://gadget-bar.local
- Админка: http://gadget-bar.local/admin
- MinIO console: http://console.gadget-bar.local

## Парсинг

```bash
docker compose -f docker-compose.prod.yml exec api php artisan import:images --type=products
```

## HTTPS

Для публичного домена добавьте к сервисам `nginx-api` и `frontend` в `docker-compose.prod.yml` labels:

```yaml
- traefik.http.routers.gb-frontend.entrypoints=websecure
- traefik.http.routers.gb-frontend.tls.certresolver=letsencrypt
```

и аналогично для `gb-api`.

## Обновление

```bash
docker compose -f docker-compose.prod.yml --env-file .env.prod.local build
docker compose -f docker-compose.prod.yml --env-file .env.prod.local up -d
```
