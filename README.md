# Gadget-Bar

Единая база данных и веб-интерфейс интернет-магазина гаджетов и аксессуаров `gbsale.ru`.

## Стек

- **Backend / сайт:** Laravel 11 (REST API + Filament admin + Blade-шаблоны)
- **База данных:** PostgreSQL 16
- **Кэш / очереди / сессии:** `database` (Redis недоступен на Beget shared hosting)
- **Поиск:** Laravel Scout + базовый драйвер
- **Хранилище файлов:** локальный диск `public` (`storage/app/public`)
- **Инфраструктура:** Docker (локальная разработка) / Beget shared hosting (prod)

## Структура

```
gb-site/
├── api/            # Laravel 11 backend + публичный сайт на Blade
├── deploy/         # Конфиги деплоя (Beget, Docker-для-истории)
├── docs/           # Документация
└── docker-compose.yml
```

## Быстрый старт (локально)

```bash
# 1. Запустить окружение
docker compose up -d

# 2. Установить зависимости backend
docker compose exec api composer install
docker compose exec api php artisan migrate

# 3. Создать администратора
docker compose exec api php artisan tinker --execute="\App\Models\User::factory()->create(['name'=>'Admin','email'=>'admin@gadget-bar.ru','password'=>bcrypt('secret')]);"
```

После этого:
- Сайт: http://localhost:8000
- API: http://localhost:8000/api
- Admin: http://localhost:8000/admin

## Деплой на прод (Beget)

```bash
python deploy_remote.py
```

Или напрямую на сервере:

```bash
cd /home/m/mastak97/gbsale.ru
bash deploy/beget/deploy.sh
```

## Документация

- [API](./docs/api.md)
- [SEO / URL](./docs/seo-urls.md)
- [Деплой](./DEPLOY.md)
