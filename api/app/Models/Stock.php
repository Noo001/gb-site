<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'store_id',
        'quantity',
        'reserved',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved' => 'decimal:2',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Склады, участвующие в продажном остатке (магазины и подразделения).
     * Service-склады (брак, уценка, тестовые, РЦ) исключаются.
     */
    public function scopeForSale($query)
    {
        return $query->whereHas('store', fn ($q) => $q->whereIn('type', [Store::TYPE_STORE, Store::TYPE_DEPARTMENT]));
    }

    /**
     * Доступное для продажи количество с учётом резерва.
     */
    public function availableQuantity(): float
    {
        return max((float) $this->quantity - (float) $this->reserved, 0);
    }
}
