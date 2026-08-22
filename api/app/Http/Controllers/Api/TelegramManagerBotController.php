<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotActionLog;
use App\Models\Store;
use App\Models\TelegramManagerEmployee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramManagerBotController extends Controller
{
    private const MAIN_MENU = [
        ['text' => 'Связаться с менеджером'],
        ['text' => 'Условия франшизы'],
        ['text' => 'Магазины'],
        ['text' => 'Главное меню'],
    ];

    public function webhook(Request $request): Response
    {
        $secret = config('services.telegram_manager.webhook_secret');
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

        // Не обрабатываем сообщения от менеджеров
        if (TelegramManagerEmployee::where('telegram_chat_id', (string) $chatId)->where('is_active', true)->exists()) {
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
        $lower = mb_strtolower($text);

        if ($lower === '/start' || $text === 'Главное меню') {
            $this->sendMainMenu($chatId);
            return;
        }

        if ($text === 'Связаться с менеджером') {
            $this->sendMessage($chatId, "Опишите ваш вопрос одним сообщением. Менеджер получит уведомление в Bitrix24 и ответит вам здесь.", $this->mainMenu());
            return;
        }

        if ($text === 'Условия франшизы') {
            $this->sendMessage($chatId, $this->franchiseConditionsText(), $this->mainMenu());
            return;
        }

        if ($text === 'Магазины') {
            $this->sendStores($chatId);
            return;
        }

        // Любое другое сообщение считаем вопросом/запросом к менеджеру
        $this->storeLead($chatId, $username, $firstName, $lastName, $text);
        $this->notifyManagers($chatId, $username, $firstName, $lastName, $text);
        $this->sendToBitrix24($chatId, $username, $firstName, $lastName, $text);

        $this->sendMessage($chatId, "Спасибо! Ваш запрос передан менеджеру. Мы ответим вам в этом чате.", $this->mainMenu());
    }

    private function handleManagerReply(array $message): void
    {
        $replyText = $message['reply_to_message']['text'] ?? '';
        $managerChatId = $message['chat']['id'] ?? null;
        $reply = trim($message['text'] ?? '');

        if (! $managerChatId || $reply === '') {
            return;
        }

        if (! preg_match('/#lead\s+(\d+)/', $replyText, $matches)) {
            return;
        }

        $clientChatId = (int) $matches[1];

        $this->sendMessage($clientChatId, $reply);
        $this->sendMessage($managerChatId, "Ответ отправлен клиенту.", $this->mainMenu());
    }

    private function sendMainMenu(int $chatId): void
    {
        $text = "Привет! Я бот Gadget Bar.\n\nЗдесь вы можете:\n• связаться с менеджером,\n• узнать условия франшизы,\n• посмотреть адреса магазинов.";
        $this->sendMessage($chatId, $text, $this->mainMenu());
    }

    private function franchiseConditionsText(): string
    {
        return "Условия франшизы Gadget Bar:\n"
            . "• Паушальный взнос — от 100 000 ₽\n"
            . "• Роялти — 1,6%\n"
            . "• Поддержка 24/7\n"
            . "• Готовая концепция, обучение, IT и маркетинг\n\n"
            . "Напишите менеджеру, и мы рассчитаем прибыль для вашего города.";
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

        $lines = ["Наши магазины:"];
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

        $this->sendMessage($chatId, implode("\n\n", $lines), $this->mainMenu());
    }

    private function mainMenu(): array
    {
        return [
            'keyboard' => array_map(fn (array $btn) => [$btn], self::MAIN_MENU),
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
    }

    private function storeLead(int $chatId, ?string $username, ?string $firstName, ?string $lastName, string $text): void
    {
        try {
            BotActionLog::create([
                'channel' => 'telegram_manager',
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
            Log::error('Failed to store Telegram manager lead', ['error' => $e->getMessage()]);
        }
    }

    private function notifyManagers(int $clientChatId, ?string $username, ?string $firstName, ?string $lastName, string $text): void
    {
        $managers = TelegramManagerEmployee::query()
            ->where('is_active', true)
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->get();

        if ($managers->isEmpty()) {
            return;
        }

        $clientLink = $username ? "@{$username}" : "tg://user?id={$clientChatId}";
        $name = trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: 'Клиент';

        $message = "📩 Новый запрос в Telegram-бот (менеджер)\n"
            . "👤 {$this->escapeHtml($name)} ({$clientLink})\n"
            . "💬 «{$this->escapeHtml($text)}»\n\n"
            . "Ответьте на это сообщение, чтобы написать клиенту.\n"
            . "#lead {$clientChatId}";

        foreach ($managers as $manager) {
            $this->sendMessage((int) $manager->telegram_chat_id, $message);
        }
    }

    private function sendToBitrix24(int $clientChatId, ?string $username, ?string $firstName, ?string $lastName, string $text): void
    {
        $webhookUrl = config('services.bitrix24.webhook_url');
        if (empty($webhookUrl)) {
            return;
        }

        $name = trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: 'Клиент из Telegram';
        $link = $username ? "https://t.me/{$username}" : "tg://user?id={$clientChatId}";

        try {
            Http::post($webhookUrl, [
                'fields' => [
                    'TITLE' => '[ТЕСТ] Запрос из Telegram-менеджер бота',
                    'NAME' => $name,
                    'COMMENTS' => "[ЭТО ТЕСТОВЫЙ ЛИД — можно удалить]\n\nИсточник: Telegram-бот менеджера\nСсылка: {$link}\nChat ID: {$clientChatId}\nСообщение:\n{$text}",
                    'SOURCE_ID' => 'TELEGRAM',
                ],
                'params' => ['REGISTER_SONET_EVENT' => 'Y'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Bitrix24 Telegram manager lead error: ' . $e->getMessage());
        }
    }

    private function sendMessage(int $chatId, string $text, ?array $keyboard = null): void
    {
        $token = config('services.telegram_manager.bot_token');
        if (! $token) {
            Log::warning('Telegram manager bot token not configured');
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
            Log::error('Telegram manager sendMessage failed', ['error' => $e->getMessage()]);
        }
    }

    public function setWebhook(): JsonResponse
    {
        $token = config('services.telegram_manager.bot_token');
        if (! $token) {
            return response()->json(['error' => 'Telegram manager bot token not configured'], 500);
        }

        $url = route('telegram.manager.webhook');
        $secret = config('services.telegram_manager.webhook_secret');
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
}
