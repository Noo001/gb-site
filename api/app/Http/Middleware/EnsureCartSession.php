<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCartSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasCookie('cart_session_id')) {
            $sessionId = bin2hex(random_bytes(16));
            config(['session.same_site' => 'lax']);
            cookie()->queue(
                cookie('cart_session_id', $sessionId, 60 * 24 * 60, '/', null, false, false, false, 'lax')
            );
            $request->cookies->set('cart_session_id', $sessionId);
        }

        $request->attributes->set('cart_session_id', $request->cookie('cart_session_id'));

        return $next($request);
    }
}
