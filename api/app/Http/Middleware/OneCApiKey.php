<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OneCApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-1C-API-Key');
        $expected = config('services.1c.api_key');

        if (empty($expected)) {
            return response()->json([
                'success' => false,
                'message' => 'API ключ 1С не настроен на сервере.',
            ], 500);
        }

        if (! hash_equals($expected, (string) $key)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный API ключ.',
            ], 401);
        }

        return $next($request);
    }
}
