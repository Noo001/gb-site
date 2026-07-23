<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = '/'.trim($request->path(), '/').'/';

        $redirect = Redirect::where('from_url', $path)
            ->where('is_active', true)
            ->first();

        if ($redirect) {
            $redirect->increment('hits');

            return redirect($redirect->to_url, $redirect->status_code);
        }

        return $next($request);
    }
}
