<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'external_id',
        'name',
        'slug',
        'default_store_id',
        'prices_store_id',
        'stocks_store_id',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function defaultStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'default_store_id');
    }

    public function pricesStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'prices_store_id');
    }

    public function stocksStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'stocks_store_id');
    }
}
