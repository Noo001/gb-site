<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: ['*']);
        $middleware->encryptCookies(except: ['cart_session_id']);
        $middleware->web(\App\Http\Middleware\EnsureCartSession::class);
        // Глобально, а не в web-группе: иначе заглушка не срабатывает на 404
        // (при отсутствии маршрута групповые middleware не выполняются).
        $middleware->append(\App\Http\Middleware\PasswordGate::class);
        // Гейт работает до StartSession (нет сессии → csrf_token() пустой),
        // поэтому его форма проверки пароля идёт без CSRF-токена.
        $middleware->validateCsrfTokens(except: ['access-check']);
        $middleware->web(\App\Http\Middleware\RedirectMiddleware::class);
        $middleware->alias([
            'onec.api' => \App\Http\Middleware\OneCApiKey::class,
            'onec.log' => \App\Http\Middleware\LogOneCRequest::class,
            'bot.api' => \App\Http\Middleware\BotApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return null;
        });

        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->segment(1) === 'api') {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
            return null;
        });
    })->create();
