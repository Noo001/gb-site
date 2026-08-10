<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotActionLog;
use App\Models\BotEmployee;
use App\Models\BotProduct;
use App\Models\BotTriggerPhrase;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramBotController extends Controller
{
    private const MAX_RESULTS = 10;

    public function webhook(Request $request): Response
    {
        $secret = config('services.telegram.webhook_secret');
        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            return response('Unauthorized', 401);
        }

        $update = $request->all();

        // Менеджер отвечает на пересланный лид
        if (! empty($update['message']['reply_to_message'])) {
            $this->handleManagerReply($update['message']);
            return response('OK', 200);
        }

        if (empty($update['message'])) {
            return response('OK', 200);
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');

        if (! $chatId || $text === '') {
            return response('OK', 200);
        }

        // Не обрабатываем сообщения от самих менеджеров (их chat_id есть в списке)
        if (BotEmployee::where('telegram_chat_id', (string) $chatId)->where('is_active', true)->exists()) {
            return response('OK', 200);
        }

        $this->handleClientMessage($chatId, $text, $message);

        return response('OK', 200);
    }

    private function handleClientMessage(int $chatId, string $text, array $message): void
    {
        $username = $message['chat']['username'] ?? null;
        $firstName = $message['chat']['first_name'] ?? null;
        $lastName = $message['chat']['last_name'] ?? null;

        // Любое входящее сообщение клиента регистрируем как лид и рассылаем менеджерам.
        $this->storeLead($chatId, $username, $firstName, $lastName, $text);
        $this->notifyManagers($chatId, $username, $firstName, $lastName, $text);

        $lower = mb_strtolower($text);

        if ($lower === '/start') {
            $this->sendMessage($chatId, "Привет! Я бот Gadget Bar.\n\nНапиши название товара — найду цены и наличие. Используй меню ниже для быстрого доступа.", $this->mainMenu());
            return;
        }

        if ($lower === '/stores' || $lower === 'магазины') {
            $this->sendStores($chatId);
            return;
        }

        if ($lower === '/tradein' || $lower === 'trade-in' || $lower === 'трейдин') {
            $this->sendMessage($chatId, "Для оценки trade-in укажи модель и состояние устройства. Например: «iPhone 14 128GB идеал». Менеджер ответит в ближайшее время.", $this->mainMenu());
            return;
        }

        $trigger = $this->checkTrigger($text);
        if ($trigger) {
            $this->sendMessage($chatId, $trigger, $this->mainMenu());
            return;
        }

        $results = $this->searchProducts($text);

        if ($results->isEmpty()) {
            $this->sendMessage($chatId, "Ничего не нашёл по запросу «{$this->escapeHtml($text)}». Попробуй написать по-другому или выбери команду из меню. Менеджер уже получил твой запрос и скоро ответит.", $this->mainMenu());
            return;
        }

        $lines = [];
        foreach ($results as $product) {
            $name = $this->escapeHtml($product->name);
            $price = number_format($product->price, 0, ',', ' ') . ' ₽';
            $stock = $product->availability === 'in_stock' ? 'в наличии' : 'нет в наличии';
            $url = $product->url ? 'https://gbsale.ru' . $product->url : '';
            $line = "• {$name}\n  {$price} — {$stock}";
            if ($url) {
                $line .= "\n  <a href=\"{$url}\">на сайт</a>";
            }
            $lines[] = $line;
        }

        $messageText = "Нашёл {$results->count()} товаров по «{$this->escapeHtml($text)}»:\n\n" . implode("\n\n", $lines);
        $this->sendMessage($chatId, $messageText, $this->mainMenu());
    }

    private function handleManagerReply(array $message): void
    {
        $replyText = $message['reply_to_message']['text'] ?? '';
        $managerChatId = $message['chat']['id'] ?? null;
        $reply = trim($message['text'] ?? '');

        if (! $managerChatId || $reply === '') {
            return;
        }

        // Ищем метку лида в пересланном сообщении: #lead <chat_id>
        if (! preg_match('/#lead\s+(\d+)/', $replyText, $matches)) {
            return;
        }

        $clientChatId = (int) $matches[1];

        $this->sendMessage($clientChatId, $reply);
        $this->sendMessage($managerChatId, "Ответ отправлен клиенту.", $this->mainMenu());
    }

    private function storeLead(int $chatId, ?string $username, ?string $firstName, ?string $lastName, string $text): void
    {
        try {
            BotActionLog::create([
                'channel' => 'telegram',
                'action' => 'lead',
                'payload' => [
                    'chat_id' => $chatId,
                    'username' => $username,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'message' => $text,
                ],
                'ip' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to store Telegram lead', ['error' => $e->getMessage()]);
        }
    }

    private function notifyManagers(int $clientChatId, ?string $username, ?string $firstName, ?string $lastName, string $text): void
    {
        $managers = BotEmployee::query()
            ->where('is_active', true)
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->get();

        if ($managers->isEmpty()) {
            return;
        }

        $clientLink = $username ? "@{$username}" : "tg://user?id={$clientChatId}";
        $name = trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: 'Клиент';

        $message = "📩 Новый лид из Telegram-бота\n"
            . "👤 {$this->escapeHtml($name)} ({$clientLink})\n"
            . "💬 «{$this->escapeHtml($text)}»\n\n"
            . "Ответь на это сообщение, чтобы написать клиенту.\n"
            . "#lead {$clientChatId}";

        foreach ($managers as $manager) {
            $this->sendMessage((int) $manager->telegram_chat_id, $message);
        }
    }

    private function searchProducts(string $query)
    {
        $term = '%' . $query . '%';

        return BotProduct::query()
            ->where('is_active', true)
            ->whereNotNull('name')
            ->where(function ($q) use ($term) {
                $q->whereLike('search_text', $term)
                    ->orWhereLike('name', $term)
                    ->orWhereLike('brand', $term)
                    ->orWhereLike('category', $term)
                    ->orWhereLike('subcategory', $term);
            })
            ->orderByRaw("CASE WHEN availability = 'in_stock' THEN 0 ELSE 1 END")
            ->orderBy('price', 'asc')
            ->limit(self::MAX_RESULTS)
            ->get();
    }

    private function checkTrigger(string $text): ?string
    {
        $message = mb_strtolower(trim($text));

        $trigger = BotTriggerPhrase::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->first(function (BotTriggerPhrase $item) use ($message) {
                $phrase = mb_strtolower($item->phrase);
                return $phrase !== '' && Str::contains($message, $phrase);
            });

        return $trigger?->response;
    }

    private function sendStores(int $chatId): void
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('name')
            ->limit(50)
            ->get();

        if ($stores->isEmpty()) {
            $this->sendMessage($chatId, "Магазинов не найдено.", $this->mainMenu());
            return;
        }

        $lines = [];
        foreach ($stores as $store) {
            $line = "• {$this->escapeHtml($store->name)}";
            if ($store->address) {
                $line .= "\n  {$this->escapeHtml($store->address)}";
            }
            if ($store->phone) {
                $line .= "\n  📞 {$store->phone}";
            }
            $lines[] = $line;
        }

        $this->sendMessage($chatId, "Наши магазины:\n\n" . implode("\n\n", $lines), $this->mainMenu());
    }

    private function mainMenu(): array
    {
        return [
            'keyboard' => [
                [['text' => 'Магазины'], ['text' => 'Trade-in']],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
    }

    private function escapeHtml(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        return str_replace(
            ['&', '<', '>', '"'],
            ['&amp;', '&lt;', '&gt;', '&quot;'],
            $text
        );
    }

    private function sendMessage(int $chatId, string $text, ?array $keyboard = null): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            Log::warning('Telegram bot token not configured');
            return;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($keyboard) {
            $payload['reply_markup'] = json_encode($keyboard);
        }

        try {
            Http::timeout(10)->post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                $payload
            );
        } catch (\Throwable $e) {
            Log::error('Telegram sendMessage failed', ['error' => $e->getMessage()]);
        }
    }

    public function setWebhook(): JsonResponse
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            return response()->json(['error' => 'Telegram bot token not configured'], 500);
        }

        $url = route('telegram.webhook');
        $secret = config('services.telegram.webhook_secret');
        $params = ['url' => $url];
        if ($secret) {
            $params['secret_token'] = $secret;
        }

        $response = Http::timeout(30)->post(
            "https://api.telegram.org/bot{$token}/setWebhook",
            $params
        );

        return response()->json($response->json() ?? ['ok' => false, 'description' => 'empty response']);
    }
}
