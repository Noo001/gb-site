# Интеграция с 1С

## Общая схема

- **Из 1С на сайт**: товары, категории, цены и остатки выгружаются по HTTP в Laravel API. Данные пишутся в staging-таблицы `1c_*` и **сразу синхронно** применяются к каталогу. 1С получает результат в том же HTTP-ответе.
- **Бот**: после изменения каталога автоматически обновляется индекс `bot_products`, по которому бот ищет товары.
- **Из сайта в 1С**: при изменении цены в админке/БД сайта ставится задача в очередь, которая делает HTTP-запрос на URL, указанный в `EXPORT_1C_WEBHOOK_URL`.

> Сейчас обмен только в одну сторону: 1С → сайт. Исходящие уведомления (сайт → 1С) будут добавлены позже.

## Настройка окружения

### Тестовый стенд

В репозитории есть файл `api/.env.testing` с тестовыми ключами:

```env
IMPORT_1C_API_KEY=test-1c-key
BOT_API_KEY=test-bot-key
EXPORT_1C_WEBHOOK_SECRET=test-1c-webhook-secret
```

Для локального тестирования и тестового стенда используйте эти ключи. Для продового контура замените на сильные уникальные значения.

### Продовый контур

Добавить в `.env.prod` на сервере:

```env
IMPORT_1C_API_KEY=<сильный-секретный-ключ>
EXPORT_1C_WEBHOOK_URL=https://1c.gb.5.3.164.6.sslip.io/hs/gadget-bar/webhook
EXPORT_1C_WEBHOOK_SECRET=<сильный-секретный-ключ-для-подписи>
EXPORT_1C_TIMEOUT=10
BOT_API_KEY=<сильный-секретный-ключ>
```

`IMPORT_1C_API_KEY` проверяется в заголовке `X-1C-API-Key`.

`BOT_API_KEY` проверяется в заголовке `X-Bot-API-Key`.

`EXPORT_1C_WEBHOOK_SECRET` используется для подписи исходящих запросов по алгоритму HMAC-SHA256.

## Эндпоинты

### Одиночная синхронизация (рекомендуется для реального времени)

Каждый запрос обрабатывается **синхронно**: 1С шлёт JSON, сайт сразу создаёт/обновляет запись и отвечает `success` или ошибкой.

#### Товар

```http
POST /api/1c/products
X-1C-API-Key: <ключ>
Content-Type: application/json
```

```json
{
  "external_id": "550e8400-e29b-41d4-a716-446655440000",
  "category_external_id": "cat-2",
  "name": "Apple iPhone 17 Pro Max 256GB Natural Titanium",
  "sku": "IP17PM256NT",
  "brand": "Apple",
  "description": "...",
  "is_active": true,
  "images_urls": ["https://1c.example.com/img1.jpg"],
  "attributes": [
    {"name": "Цвет", "value": "Natural Titanium"},
    {"name": "Память", "value": "256 GB"}
  ],
  "price": 149990,
  "currency": "RUB",
  "quantity": 5,
  "store_external_id": "main"
}
```

Если нужно несколько офферов на один товар:

```json
{
  "external_id": "550e8400-e29b-41d4-a716-446655440000",
  "name": "iPhone 17 Pro Max",
  "offers": [
    {
      "external_id": "550e8400-...-offer-1",
      "name": "iPhone 17 Pro Max 256GB Natural Titanium",
      "sku": "IP17PM256NT",
      "barcode": "1234567890123",
      "prices": [
        {"price_type": "retail", "price": 149990, "currency": "RUB"}
      ],
      "stocks": [
        {"store_external_id": "main", "quantity": 5}
      ]
    }
  ]
}
```

#### Категория

```http
POST /api/1c/categories
X-1C-API-Key: <ключ>
Content-Type: application/json
```

```json
{
  "external_id": "cat-2",
  "parent_external_id": "cat-1",
  "name": "Apple",
  "is_active": true,
  "sort": 10
}
```

#### Цена

```http
POST /api/1c/prices
X-1C-API-Key: <ключ>
Content-Type: application/json
```

```json
{
  "offer_external_id": "550e8400-...-offer-1",
  "price_type": "retail",
  "price": 139990,
  "currency": "RUB"
}
```

#### Остаток

```http
POST /api/1c/stocks
X-1C-API-Key: <ключ>
Content-Type: application/json
```

```json
{
  "offer_external_id": "550e8400-...-offer-1",
  "store_external_id": "main",
  "quantity": 3
}
```

Если склад с `store_external_id` не существует на сайте, он создаётся автоматически.

#### Снятие с продажи / удаление товара

```http
POST /api/1c/products/delete
X-1C-API-Key: <ключ>
Content-Type: application/json
```

Снять с продажи (`is_active = false`):

```json
{
  "external_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

Удалить полностью (soft delete):

```json
{
  "external_id": "550e8400-e29b-41d4-a716-446655440000",
  "permanent": true
}
```

### Bulk-синхронизация (для начальной загрузки)

```http
POST /api/1c/bulk-sync
X-1C-API-Key: <ключ>
Content-Type: application/json
```

Принимает пакет данных: категории, товары, офферы, цены, остатки. Записывает в staging и ставит в очередь `Apply1CStagingData`. Возвращает `batch_id`.

```json
{
  "categories": [
    {"external_id": "cat-1", "parent_external_id": null, "name": "Смартфоны", "is_active": true}
  ],
  "products": [
    {
      "external_id": "550e8400-e29b-41d4-a716-446655440000",
      "category_external_id": "cat-1",
      "name": "iPhone 17 Pro Max",
      "sku": "IP17PM256NT",
      "brand": "Apple",
      "is_active": true,
      "price": 149990,
      "currency": "RUB",
      "quantity": 5
    }
  ]
}
```

### Статус bulk-синхронизации

```http
GET /api/1c/bulk-sync/{batch_id}/status
X-1C-API-Key: <ключ>
```

Возвращает текущую статистику по пакету.

### Синхронизация товара (устаревший, одиночный)

```http
POST /api/1c/products/sync
PUT  /api/1c/products/sync
X-1C-API-Key: <ключ>
Content-Type: application/json
```

Старый endpoint. Работает без staging — сразу создаёт/обновляет товар.

### Пакетное обновление цен

```http
POST /api/1c/prices/sync
PUT  /api/1c/prices/sync
X-1C-API-Key: <ключ>
Content-Type: application/json
```

```json
{
  "items": [
    {
      "uuid_1c": "550e8400-e29b-41d4-a716-446655440000",
      "price": 139990,
      "currency": "RUB"
    }
  ]
}
```

### Список товаров

```http
GET /api/1c/products?updated_since=2026-06-01T00:00:00+03:00&limit=100&offset=0
X-1C-API-Key: <ключ>
```

### Карточка товара

```http
GET /api/1c/products/{uuid_1c}
X-1C-API-Key: <ключ>
```

## Бот первой линии

Бот не ходит напрямую в каталог. Он ищет по таблице `bot_products`, которая является **read-only индексом** для быстрого поиска.

### Как обновляется индекс бота

| Сценарий | Что происходит |
|---|---|
| Одиночный запрос (`/api/1c/products`, `/api/1c/prices`, `/api/1c/stocks`) | Соответствующий товар/оффер обновляется в `bot_products` сразу после применения. |
| Bulk-запрос (`/api/1c/bulk-sync`) | После успешного применения всех staging-записей автоматически запускается полное перестроение индекса `bot_products`. |
| Ручной запуск | `POST /api/1c/bot/rebuild-index` ставит задачу на полное перестроение индекса. |
| По расписанию | `php artisan bot:rebuild-index` выполняется каждый час через Laravel Scheduler. |

### Поля, важные для бота

| Для бота | Поле в 1С | Куда на сайте |
|---|---|---|
| Название | `Наименование` | `products.name`, `offers.name` |
| Артикул | `Артикул` | `products.sku`, `offers.sku` |
| Бренд | Производитель | `products.brand` |
| Категория | Иерархия номенклатуры | `categories` |
| Цена | Цена продажи | `prices` |
| Остаток | Свободный остаток | `stocks` |
| Склад/город | Склад | `stores.city` → `available_in_cities` |
| Атрибуты | Свойства | `attributes` → `metadata` |

Бот автоматически извлекает метаданные из атрибутов по названию: `Цвет` → `color`, `Память`/`Объём` → `storage`, `SIM` → `sim_type`, `Оперативная память` → `ram_gb`, `Процессор` → `cpu`.

## Применение данных

Для одиночных endpoints данные применяются сразу в HTTP-запросе.

Для `bulk-sync` данные попадают в таблицы `1c_*`, и job `Apply1CStagingData` применяет их в фоне. Требуется запущенный queue worker:

```bash
php artisan queue:work
```

## Исходящее уведомление при изменении цены

При любом изменении/создании цены в БД сайта (кроме синхронизации из 1С) Laravel ставит в очередь `NotifyPriceChangedTo1C`. Задача делает POST-запрос:

```http
POST <EXPORT_1C_WEBHOOK_URL>
Content-Type: application/json
```

```json
{
  "uuid_1c": "550e8400-e29b-41d4-a716-446655440000",
  "new_price": 139990,
  "changed_at": "2026-06-28T12:00:00+03:00",
  "source": "admin_panel"
}
```

> В ближайших обновлениях формат будет приведён к единому событию с подписью HMAC-SHA256:
>
> ```json
> {
>   "event": "price_changed",
>   "timestamp": "2026-06-30T17:00:00+03:00",
>   "signature": "hmac-sha256=...",
>   "payload": { "uuid_1c": "...", "new_price": 139990, "currency": "RUB" }
> }
> ```

## Пример кода для 1С

Ниже минимальный пример HTTP-соединения из 1С к сайту. В реальном решении рекомендуется вынести параметры (адрес, ключ) в константы/регистр сведений.

### Отправка одного товара

```1c
&НаСервере
Процедура ОтправитьТоварНаСайт(Номенклатура) Экспорт

    АдресСайта = "https://gb.5.3.164.6.sslip.io";
    КлючAPI    = "<IMPORT_1C_API_KEY>";

    Соединение = Новый HTTPСоединение(
        АдресСайта,
        443,
        , , ,
        20,
        Новый ЗащищенноеСоединениеOpenSSL,
        Ложь
    );

    Запрос = Новый HTTPЗапрос("/api/1c/products");
    Запрос.Заголовки.Вставить("X-1C-API-Key", КлючAPI);
    Запрос.Заголовки.Вставить("Content-Type", "application/json");

    Тело = Новый Структура;
    Тело.Вставить("external_id", Строка(Номенклатура.УникальныйИдентификатор()));
    Тело.Вставить("name", Номенклатура.Наименование);
    Тело.Вставить("sku", Номенклатура.Артикул);
    Тело.Вставить("price", 149990);
    Тело.Вставить("currency", "RUB");
    Тело.Вставить("quantity", 5);

    Запрос.УстановитьТелоИзСтроки(
        ЗаписатьJSON(Тело),
        КодировкаТекста.UTF8,
        ИспользованиеByteOrderMark.НеИспользовать
    );

    Ответ = Соединение.ОтправитьДляОбработки(Запрос);

    Если Ответ.КодСостояния <> 200 Тогда
        ЗаписьЖурналаРегистрации(
            "GadgetBar.ОбменТоварами",
            УровеньЖурналаРегистрации.Ошибка,
            ,,
            "Ошибка выгрузки товара: " + Ответ.ПолучитьТелоКакСтроку()
        );
    КонецЕсли;

КонецПроцедуры
```

## Логирование

Все входящие и исходящие запросы 1С пишутся в таблицу `integration_logs`.
