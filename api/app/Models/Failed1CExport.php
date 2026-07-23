<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Failed1CExport extends Model
{
    use HasFactory;

    protected $table = 'failed_1c_exports';

    protected $fillable = [
        'payload',
        'endpoint',
        'attempts',
        'error_message',
        'processed_at',
        'failed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query
            ->whereNull('processed_at')
            ->whereNull('failed_at')
            ->where('attempts', '<', config('services.1c.max_queue_attempts', 10));
    }

    public function markProcessed(): void
    {
        $this->update([
            'processed_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ]);
    }

    public function markFailed(string $message, ?int $maxAttempts = null): void
    {
        $maxAttempts ??= config('services.1c.max_queue_attempts', 10);
        $attempts = $this->attempts + 1;

        $this->update([
            'attempts' => $attempts,
            'error_message' => $message,
            'failed_at' => $attempts >= $maxAttempts ? now() : null,
        ]);
    }
}
