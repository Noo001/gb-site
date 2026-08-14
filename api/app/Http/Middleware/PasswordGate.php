<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PasswordGate
{
    public const PASSWORD = '111';
    public const COOKIE_NAME = 'site_access';
    public const COOKIE_VALUE = 'granted';

    public function handle(Request $request, Closure $next): Response
    {
        // Заглушка на вход отключена по требованию заказчика (август 2026).
        // Оставляем класс и константы на случай, если понадобится вернуть позже.
        return $next($request);
    }
}
