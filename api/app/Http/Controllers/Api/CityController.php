<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class CityController extends Controller
{
    private const DEFAULT_CITY = 'Воронеж';

    private const KNOWN_CITIES = [
        'Москва', 'Санкт-Петербург', 'Воронеж', 'Липецк', 'Белгород',
        'Краснодар', 'Старый Оскол', 'Тамбов', 'Пермь', 'Нижний Новгород',
        'Уфа', 'Ижевск', 'Казань', 'Калуга', 'Ростов-на-Дону', 'Ярославль',
    ];

    public function detect(Request $request): JsonResponse
    {
        $ip = $this->clientIp($request);

        try {
            $url = $ip ? "https://ipwho.is/{$ip}" : 'https://ipwho.is/';
            $response = Http::timeout(5)->get($url);

            if (! $response->successful()) {
                return $this->defaultCity();
            }

            $data = $response->json();
            $city = $data['city'] ?? null;

            if (! $city || ! is_string($city)) {
                return $this->defaultCity();
            }

            $matched = $this->matchCity($city);

            return response()->json([
                'city' => $matched ?? self::DEFAULT_CITY,
                'detected' => $city,
            ]);
        } catch (Throwable $e) {
            return $this->defaultCity();
        }
    }

    private function clientIp(Request $request): ?string
    {
        $forwarded = $request->header('X-Forwarded-For');

        if ($forwarded) {
            $ips = array_map('trim', explode(',', $forwarded));
            $ip = $ips[0];
        } else {
            $ip = $request->ip();
        }

        if (! $ip || $ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '10.') || str_starts_with($ip, '192.168.')) {
            return null;
        }

        // Keep public IPs only
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ?: null;
    }

    private function matchCity(string $detected): ?string
    {
        $lower = mb_strtolower($detected);

        foreach (self::KNOWN_CITIES as $city) {
            if (mb_strtolower($city) === $lower) {
                return $city;
            }
        }

        return null;
    }

    private function defaultCity(): JsonResponse
    {
        return response()->json([
            'city' => self::DEFAULT_CITY,
            'detected' => null,
        ]);
    }
}
