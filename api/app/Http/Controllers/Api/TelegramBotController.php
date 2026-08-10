<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        if (mb_strtolower($text) === '/start') {
            $this->sendMessage($chatId, "Привет! Напиши название товара — найду цены и наличие.");
            return;
        }

        $results = $this->searchProducts($text);

        if ($results->isEmpty()) {
            $this->sendMessage($chatId, "Ничего не нашёл по запросу «{$text}». Попробуй написать по-другому.");
            return;
        }

        $lines = [];
        foreach ($results as $product) {
            $name = $product->name;
            $price = number_format($product->price, 0, ',', ' ') . ' ₽';
            $stock = $product->availability === 'in_stock' ? 'в наличии' : 'нет в наличии';
            $url = $product->url ? 'https://gbsale.ru' . $product->url : '';
            $line = "• {$name}\n  {$price} — {$stock}";
            if ($url) {
                $line .= "\n  {$url}";
            }
            $lines[] = $line;
        }

        $message = "Нашёл {$results->count()} товаров по «{$text}»:\n\n" . implode("\n\n", $lines);
        $this->sendMessage($chatId, $message);
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

    private function sendMessage(int $chatId, string $text): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            Log::warning('Telegram bot token not configured');
            return;
        }

        try {
            Http::timeout(10)->post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]
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
