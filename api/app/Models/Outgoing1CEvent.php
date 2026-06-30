<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outgoing1CEvent extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    public const EVENT_PRICE_CHANGED = 'price_changed';
    public const EVENT_STOCK_CHANGED = 'stock_changed';
    public const EVENT_PRODUCT_CREATED = 'product_created';
    public const EVENT_PRODUCT_UPDATED = 'product_updated';
    public const EVENT_ORDER_CREATED = 'order_created';

    protected $fillable = [
        'event_type',
        'payload',
        'status',
        'attempts',
        'last_error',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'attempts' => $this->attempts + 1,
            'sent_at' => now(),
            'last_error' => null,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'attempts' => $this->attempts + 1,
            'last_error' => $error,
        ]);
    }

    public function markAsPending(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'last_error' => null,
        ]);
    }
}
