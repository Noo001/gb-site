# Gadget-Bar

Переписанный сайт интернет-магазина гаджетов и аксессуаров.

## Стек

- **Frontend:** Next.js 16 (App Router), React 19, TypeScript, Redux Toolkit, Tailwind CSS
- **Backend:** Laravel 11 (REST API + Filament admin)
- **База данных:** PostgreSQL 16
- **Кэш / очереди / сессии:** Redis
- **Поиск:** Meilisearch
- **Хранилище файлов:** S3-совместимое (MinIO для локальной разработки)
- **Инфраструктура:** Docker + Docker Compose

## Структура

```
gb-site/
├── api/            # Laravel 11 backend
├── frontend/       # Next.js frontend
├── docker/         # Docker-конфиги
├── docs/           # Документация
└── docker-compose.yml
```

## Быстрый старт

```bash
# 1. Запустить окружение
docker compose up -d

# 2. Установить зависимости backend
docker compose exec api composer install
docker compose exec api php artisan migrate

# 3. Создать администратора
docker compose exec api php artisan tinker --execute="\App\Models\User::factory()->create(['name'=>'Admin','email'=>'admin@gadget-bar.ru','password'=>bcrypt('secret')]);

# 4. Установить зависимости frontend (внутри Linux-контейнера, чтобы нативные модули собрались под Linux)
docker compose run --rm frontend bash -c "cd /var/www/frontend && npm install"
```

После этого:
- Frontend: http://localhost:3000
- API: http://localhost:8000
- Admin: http://localhost:8000/admin
- MinIO: http://localhost:9000

## Разработка

```bash
# Backend
docker compose exec api bash

# Frontend
docker compose exec frontend bash

# Миграции
docker compose exec api php artisan migrate

# Очереди обрабатываются сервисом queue автоматически
```

## Документация

- [API](./docs/api.md)
- [SEO / URL](./docs/seo-urls.md)
- [Деплой](./docs/deploy.md)
