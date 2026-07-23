# Интеграция первой линии (n8n → сайт)

Бот-firstline, экспортированный в `docs/GadgetBar_Bitrix24_v3.json`, теперь ходит за данными в БД сайта через выделенный read-only API.

## Архитектура

- Бот никогда не подключается к БД напрямую.
- Все запросы идут в Laravel API `/api/bot/*` с заголовком `X-Bot-API-Key`.
- Данные для поиска товаров агрегируются в отдельную таблицу `bot_products`.
- Справочные данные (конфиги, сервисы, триггеры, trade-in) хранятся в `bot_knowledge` и `bot_trade_in_prices`.
- Лог действий бота пишется в `bot_action_logs` (единственная запись, остальные эндпоинты read-only).

## Настройка

### 1. Добавить env-переменную на сервере

В `.env.prod`:

```env
BOT_API_KEY=<сильный-случайный-ключ>
```

### 2. Выполнить миграции

```bash
docker exec -it gb-api php artisan migrate
```

### 3. Заполнить индекс товаров

```bash
docker exec -it gb-api php artisan bot:rebuild-index
```

Команда пересобирает `bot_products` из актуальных `products`/`offers`/`prices`/`stocks`/`attributes`. Запускается автоматически каждый час — для этого в `docker-compose.caddy.yml` добавлен сервис `gb-scheduler`, который выполняет `php artisan schedule:work`.

### 4. Заполнить справочники

Примеры записей в `bot_knowledge`:

```sql
-- конфигурация
INSERT INTO bot_knowledge (type, "group", key, payload, sort, is_active)
VALUES ('config', 'payment', 'installments', '{"title":"Рассрочка","text":"Доступна рассрочка 0%."}', 1, true);

-- сервисы
INSERT INTO bot_knowledge (type, "group", key, payload, sort, is_active)
VALUES ('service', 'repair', 'iPhone 16 Pro', '{"title":"Замена экрана iPhone 16 Pro","price":25000}', 1, true);

-- триггерные фразы
INSERT INTO bot_knowledge (type, "group", key, payload, sort, is_active)
VALUES ('trigger', 'escalation', 'оператор', '{"action":"escalate_to_manager","message":"Сейчас переведу на менеджера."}', 1, true);
```

Trade-in — таблица `bot_trade_in_prices`.

## Эндпоинты

| URL | Назначение |
|---|---|
| `POST /api/bot/products/search` | Поиск товаров |
| `POST /api/bot/alternatives` | Альтернативы, если товара нет в наличии |
| `POST /api/bot/services` | Поиск услуг/ремонта |
| `POST /api/bot/triggers/check` | Проверка триггерных фраз |
| `POST /api/bot/config` | Политики: оплата, доставка, гарантия, возврат и т.д. |
| `POST /api/bot/stores` | Магазины и контакты |
| `POST /api/bot/tradein` | Оценочные цены Trade-In |
| `POST /api/bot/log` | Лог действия бота |

Все запросы требуют заголовок:

```http
X-Bot-API-Key: <BOT_API_KEY>
```

## Импорт workflow в n8n

1. Открыть n8n → Workflows → Import from file.
2. Выбрать `docs/GadgetBar_Bitrix24_v3.json`.
3. В настройках окружения n8n задать переменную `BOT_API_KEY`.
4. Workflow уже настроен на URL сайта и использует `={{ $env.BOT_API_KEY }}`.

## Обновление workflow при изменениях

Если структура запросов бота меняется, обновите файл `docs/GadgetBar_Bitrix24_v3.json` в репозитории и используйте утилиту:

```bash
python3 tools/update_bot_workflow.py
```

Она перезаписывает Supabase-URL'ы на URL сайта.
