<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotProduct;
use App\Models\BotTradeInPrice;
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
        if (empty($update['message'])) {
            return response('OK', 200);
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');

        if (! $chatId || $text === '') {
            return response('OK', 200);
        }

        $this->handleMessage($chatId, $text);

        return response('OK', 200);
    }

    private function handleMessage(int $chatId, string $text): void
    {
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
            $this->sendMessage($chatId, "Для оценки trade-in укажи модель и состояние устройства. Например: «iPhone 14 128GB идеал».", $this->mainMenu());
            return;
        }

        $trigger = $this->checkTrigger($text);
        if ($trigger) {
            $this->sendMessage($chatId, $trigger, $this->mainMenu());
            return;
        }

        $results = $this->searchProducts($text);

        if ($results->isEmpty()) {
            $this->sendMessage($chatId, "Ничего не нашёл по запросу «{$text}». Попробуй написать по-другому или выбери команду из меню.", $this->mainMenu());
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

        $message = "Нашёл {$results->count()} товаров по «{$this->escapeHtml($text)}»:\n\n" . implode("\n\n", $lines);
        $this->sendMessage($chatId, $message, $this->mainMenu());
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
