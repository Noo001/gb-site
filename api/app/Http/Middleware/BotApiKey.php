<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BotApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Bot-API-Key');
        $expected = config('services.bot.api_key');

        if (empty($expected)) {
            return response()->json([
                'success' => false,
                'message' => 'API ключ бота не настроен на сервере.',
            ], 500);
        }

        if (! hash_equals($expected, (string) $key)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный API ключ бота.',
            ], 401);
        }

        return $next($request);
    }
}
