<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public static array $statuses = [
        self::STATUS_PENDING => 'Новая',
        self::STATUS_PROCESSING => 'В обработке',
        self::STATUS_COMPLETED => 'Выполнена',
        self::STATUS_CANCELLED => 'Отменена',
    ];

    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_city',
        'customer_comment',
        'manager_comment',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('id');
    }

    public function statusLabel(): string
    {
        return self::$statuses[$this->status] ?? $this->status;
    }
}
