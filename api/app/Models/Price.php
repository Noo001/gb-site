<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasFactory;

    public static bool $syncingFrom1C = false;

    protected $fillable = [
        'offer_id',
        'region_id',
        'store_id',
        'price',
        'old_price',
        'currency',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public static function withoutSyncNotifications(callable $callback): mixed
    {
        $previous = self::$syncingFrom1C;
        self::$syncingFrom1C = true;
        try {
            return $callback();
        } finally {
            self::$syncingFrom1C = $previous;
        }
    }
}
