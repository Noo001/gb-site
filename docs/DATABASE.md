# База данных — описание таблиц

БД: PostgreSQL (Beget cloud, `oritharuskog.beget.app:5432`, база `default_db`).
Бэкенд: Laravel 11 (`api/`), миграции в `api/database/migrations/`.

## Принципы

- **Два идентификатора у каждой сущности**: внутренний `id` (bigint, автоинкремент) — для связей внутри базы; внешний UUID из 1С — в отдельных колонках (`uuid_1c` / `external_id`). Мэтчинг с 1С идёт только по внешнему ID, уникальность по нему обеспечена логикой `updateOrCreate`.
- Повторная выгрузка из 1С **обновляет** существующие записи, не создаёт дубли.
- `created_at` / `updated_at` — метки создания и последнего изменения. `deleted_at` — мягкое удаление (SoftDeletes).

---

## Каталог

### `products` — товары
| Колонка | Назначение |
|---|---|
| `uuid_1c` | **UUID номенклатуры из 1С** — ключ мэтчинга |
| `category_id` | → `categories.id` |
| `name`, `slug`, `url` | Название, ЧПУ, публичный URL |
| `sku` | Артикул |
| `brand` | Бренд |
| `description`, `content` | Описания |
| `warranty_months` | Гарантия |
| `is_active` | Видимость. Товар без цены создаётся неактивным, активируется приходом цены > 0 |

### `offers` — торговые предложения (SKU)
| Колонка | Назначение |
|---|---|
| `external_id` | **UUID из 1С** — ключ мэтчинга (обычно = UUID номенклатуры) |
| `product_id` | → `products.id` |
| `name`, `sku`, `barcode` | Название, артикул, штрихкод |
| `is_active` | Видимость |

### `prices` — цены
Одна запись на комбинацию `offer_id` + `region_id` + `store_id` (перезаписывается).
| Колонка | Назначение |
|---|---|
| `offer_id` | → `offers.id` |
| `region_id`, `store_id` | NULL = базовая цена (текущий режим работы с 1С) |
| `price`, `old_price`, `currency` | Цена, старая цена, валюта |

### `stocks` — остатки
Одна запись на пару `offer_id` + `store_id` (перезаписывается **срезом** из 1С).
| Колонка | Назначение |
|---|---|
| `offer_id` | → `offers.id` |
| `store_id` | → `stores.id`, NULL = без склада |
| `quantity` | Текущий остаток (актуальное значение регистра «Запасы») |
| `reserved` | Резерв (вычитается при расчёте доступности) |

### `stores` — склады/магазины
| Колонка | Назначение |
|---|---|
| `external_id` | **UUID структурной единицы из 1С** — ключ мэтчинга |
| `name` | Название (синхронизируется из 1С) |
| `city` | Город. Из 1С приходит если есть, пустым не затирается; можно править в админке |
| `address`, `phone`, `email`, `schedule` | Контакты |
| `latitude`, `longitude` | Координаты |
| `sort`, `is_active` | Порядок, видимость |

### `categories` — категории
| Колонка | Назначение |
|---|---|
| `external_id` | UUID категории из 1С (если приходит) |
| `parent_id` | → `categories.id` (иерархия) |
| `name`, `slug`, `full_path`, `url` | Навигация |
| `sort`, `is_active` | Порядок, видимость |
| `seo_*` | SEO-поля |

### `attributes` — характеристики товаров
Справочник атрибутов: `external_id`, `name`, `slug`, `type`, `unit`, `sort`, `is_active`, `is_filter`.

### `product_attribute_values` — значения характеристик
`product_id`, `attribute_id`, `offer_id` (NULL = общее для товара), `value`.

### `regions` — регионы
`external_id`, `name`, `slug`, `default_store_id`, `prices_store_id`, `stocks_store_id`, `is_default`, `is_active`. Для региональных цен/остатков (пока не используется в обмене с 1С).

### `media` — медиафайлы (spatie/laravel-medialibrary)
Картинки товаров: `model_type`/`model_id` — владелец, `collection_name` (`images`), `file_name`, `disk`, `size`, `uuid`.

---

## Интеграция с 1С

### Staging-таблицы (приёмник пакетов перед применением)
`1c_categories`, `1c_offers`, `1c_prices`, `1c_products`, `1c_stocks` — единая структура:

| Колонка | Назначение |
|---|---|
| `external_id` / `offer_external_id` / `product_external_id` / `category_external_id` / `store_external_id` | UUID из 1С |
| `name`, `sku`, `barcode`, `price`, `price_type`, `currency`, `quantity` | Данные (зависят от таблицы) |
| `raw` | Исходный JSON целиком |
| `batch_id` | ID пакета (bulk-sync) |
| `processed_at` | Когда применено в основные таблицы |
| `attempts`, `error` | Попытки и текст ошибки |

Поток: 1С → staging → `OneCSyncService::apply()` → основные таблицы.

### `failed_1c_exports` — отложенные экспорты
Записи, которые не удалось применить сразу: `endpoint`, `payload`, `attempts`, `error_message`, `processed_at`, `failed_at`. Обрабатываются каждую минуту командой `1c:process-queue`.

### `integration_logs` — журнал обмена
Каждый HTTP-запрос интеграции: `direction` (in/out), `system` (1c), `endpoint`, `method`, `payload`, `headers`, `response`, `status_code`, `duration_ms`, `ip`, `error`.

### `outgoing_1c_events` — исходящие события в 1С
Задел на будущее (`price_changed`, `order_created`...): `event_type`, `payload`, `status` (pending/sent/failed), `attempts`, `last_error`, `sent_at`. Сейчас не используется.

---

## Бот первой линии

### `bot_products` — поисковый индекс бота
Денормализованная проекция каталога, перестраивается (`bot:rebuild-index`) и точечно обновляется при каждом событии синхронизации. Одна запись на offer, товары без цены > 0 не включаются.
| Колонка | Назначение |
|---|---|
| `offer_id`, `product_id` | Ссылки на каталог |
| `name`, `brand`, `category`, `subcategory` | Отображение/поиск |
| `price`, `old_price`, `currency` | Цена (минимальная положительная) |
| `availability` | `in_stock` / `out_of_stock` |
| `quantity` | Сумма доступного (quantity − reserved) по складам |
| `available_in_cities`, `city_availability` | Наличие по городам (JSON) |
| `metadata` | Цвет/память/SIM и пр. (JSON, из атрибутов) |
| `search_text` | Собранный текст для поиска |
| `url`, `image_url`, `is_active`, `updated_at` | Прочее |

### `bot_knowledge` — база знаний бота
`type`, `group`, `key`, `payload` (JSON), `sort`, `is_active`.

### `bot_trade_in_prices` — прайс trade-in
`brand`, `model`, `storage`, `condition`, `price`, `currency`, `is_active`.

### `bot_action_logs` — журнал действий бота
`channel`, `action`, `payload`, `metadata`, `ip`.

---

## Магазин (витрина)

- **`users`** — пользователи: `name`, `email`, `phone`, `password` (хэш).
- **`social_accounts`** — привязки соцсетей: `provider`, `provider_id`, `payload`.
- **`cart_items`** — корзина: `user_id` или `session_id`, `offer_id`, `quantity`.
- **`wishlist_items`** — избранное: `user_id`, `offer_id`.
- **`orders`** — заказы: `user_id`/`session_id`, `status`, `customer_*` (имя/телефон/email/город/комментарий), `total`.
- **`order_items`** — позиции заказа: `order_id`, `offer_id`, `product_name`, `offer_name`, `quantity`, `price`, `total`.
- **`pages`** — статические страницы: `url`, `type`, `title`, `content`.
- **`redirects`** — редиректы: `from_url`, `to_url`, `status_code`, `hits`.
- **`seo_metadata`** — SEO для сущностей: `entity_type`/`entity_id`, `title`, `description`, `json_ld` и др.

---

## Системные (Laravel)

- **`jobs`** — очередь задач; **`job_batches`** — пакеты задач; **`failed_jobs`** — упавшие задачи.
- **`cache`**, **`cache_locks`** — кэш (database driver).
- **`sessions`** — сессии.
- **`personal_access_tokens`** — токены Sanctum (API-авторизация).
- **`password_reset_tokens`** — сброс пароля.
- **`migrations`** — журнал миграций.

---

## Контрольные соотношения (проверка на дубли)

При здоровом обмене выполняется:

- `prices` ≈ `offers` (по одной цене на offer при текущем режиме);
- `stocks` = числу уникальных пар offer×склад с ненулевыми остатками;
- `bot_products` = числу активных offers с ценой > 0;
- дубликаты по `uuid_1c` / `external_id` / (offer, store) — всегда 0.

Проверочные SQL-запросы — см. историю сессии 2026-07-23 (выдают 0 по всем таблицам).
