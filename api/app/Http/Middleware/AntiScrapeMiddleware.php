<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AntiScrapeMiddleware
{
    /**
     * Max requests per minute from a single IP for HTML pages.
     */
    private const MAX_REQUESTS_PER_MINUTE = 120;

    /**
     * Known friendly bot substrings we do not want to block accidentally.
     */
    private const GOOD_BOTS = [
        'googlebot',
        'yandexbot',
        'bingbot',
        'duckduckbot',
        'slurp',
        'ahrefsbot',
        'semrushbot',
    ];

    /**
     * Cheap client-side visitor cookie. Scrapers that do not persist cookies
     * stay identifiable, but we never block on the first request.
     */
    private const VISITOR_COOKIE = 'gb_visitor';

    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        // Skip health endpoint and local CLI routes.
        if ($request->is('up', 'healthz')) {
            return $next($request);
        }

        $ip = $request->ip() ?? 'unknown';
        $ua = (string) $request->userAgent();
        $uaLower = mb_strtolower($ua);

        // Block completely empty user-agents.
        if ($ua === '') {
            return $this->forbidden('Missing User-Agent');
        }

        // Block known headless / scraping tools. Keep search engines allowed.
        if ($this->isBadBot($uaLower)) {
            return $this->forbidden('Automated access is not allowed.');
        }

        // Rate limit per IP for the web front-end.
        $key = 'anti-scrape:' . $ip;
        if (RateLimiter::tooManyAttempts($key, self::MAX_REQUESTS_PER_MINUTE)) {
            return response('Too many requests. Please slow down.', 429)
                ->header('Retry-After', RateLimiter::availableIn($key));
        }
        RateLimiter::hit($key, 60);

        $response = $next($request);

        // Set a visitor cookie if missing. Valid browsers will keep sending it;
        // simple curl scripts without cookie jar will not, which helps logs only.
        if (! $request->cookie(self::VISITOR_COOKIE)) {
            $response->cookie(
                cookie(self::VISITOR_COOKIE, hash('sha256', $ip . config('app.key')), 60 * 24 * 365)
            );
        }

        return $response;
    }

    private function isBadBot(string $ua): bool
    {
        $badSignatures = [
            'curl',
            'wget',
            'python-requests',
            'scrapy',
            'httpclient',
            'java/',
            'axios',
            'postman',
            'insomnia',
            'headlesschrome',
            'phantomjs',
            'selenium',
            'puppeteer',
            'playwright',
        ];

        foreach ($badSignatures as $sig) {
            if (str_contains($ua, $sig)) {
                // Do not block major search-engine crawlers that sometimes include "chrome" etc.
                foreach (self::GOOD_BOTS as $good) {
                    if (str_contains($ua, $good)) {
                        return false;
                    }
                }
                return true;
            }
        }

        return false;
    }

    private function forbidden(string $message): Response
    {
        return response($message, 403)
            ->header('Cache-Control', 'no-store');
    }
}
