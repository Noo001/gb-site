<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PasswordGate
{
    public const PASSWORD = '111';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin*', 'api/*', 'up', '_debugbar/*', 'images/*', 'css/*', 'js/*', 'favicon.ico', 'livewire*', 'pc*')) {
            return $next($request);
        }

        if ($request->isMethod('post') && $request->is('access-check')) {
            return $next($request);
        }

        // Client-side gate: the user must visit with ?site_access=granted after
        // entering the password. sessionStorage is checked by the gate page JS.
        if ($request->query('site_access') === 'granted') {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Требуется пароль.'], 403);
        }

        return response()->view('auth.password-gate', [], 403);
    }
}
