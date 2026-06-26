<?php

namespace App\Jobs;

use App\Models\IntegrationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class NotifyPriceChangedTo1C implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = [30, 120, 300];

    public function __construct(
        public string $uuid1c,
        public float $newPrice,
        public string $changedAt,
        public string $source
    ) {
    }

    public function handle(): void
    {
        $url = config('services.1c.webhook_url');
        $timeout = (int) config('services.1c.timeout', 10);

        $payload = [
            'uuid_1c' => $this->uuid1c,
            'new_price' => $this->newPrice,
            'changed_at' => $this->changedAt,
            'source' => $this->source,
        ];

        $startedAt = microtime(true);
        $statusCode = null;
        $responseBody = null;
        $error = null;

        if (empty($url)) {
            $error = 'Webhook URL для 1С не настроен.';
        } else {
            try {
                $response = Http::timeout($timeout)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                    ->post($url, $payload);

                $statusCode = $response->status();
                $responseBody = $response->body();

                if (! $response->successful()) {
                    $error = "HTTP {$statusCode}: " . mb_strimwidth($responseBody, 0, 500);
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        IntegrationLog::create([
            'direction' => IntegrationLog::DIRECTION_OUT,
            'system' => '1c',
            'endpoint' => $url ?: 'not_configured',
            'method' => 'POST',
            'payload' => $payload,
            'headers' => ['Content-Type' => 'application/json'],
            'response' => $responseBody ? ['body' => $responseBody] : null,
            'status_code' => $statusCode,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'error' => $error,
        ]);

        if ($error) {
            throw new \RuntimeException($error);
        }
    }
}
