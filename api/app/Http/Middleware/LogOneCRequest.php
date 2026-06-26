<?php

namespace App\Http\Middleware;

use App\Models\IntegrationLog;
use Closure;
use Illuminate\Contracts\Routing\TerminableMiddleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogOneCRequest implements TerminableMiddleware
{
    private float $startedAt;

    public function handle(Request $request, Closure $next): Response
    {
        $this->startedAt = microtime(true);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $duration = (int) round((microtime(true) - $this->startedAt) * 1000);

        $payload = $request->getContent();
        $decodedPayload = json_validate($payload) ? json_decode($payload, true) : null;

        IntegrationLog::create([
            'direction' => IntegrationLog::DIRECTION_IN,
            'system' => '1c',
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'payload' => $decodedPayload,
            'headers' => $this->filteredHeaders($request),
            'response' => json_decode($response->getContent(), true),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'ip' => $request->ip(),
        ]);
    }

    private function filteredHeaders(Request $request): array
    {
        $headers = $request->headers->all();
        unset($headers['x-1c-api-key']);

        return $headers;
    }
}
