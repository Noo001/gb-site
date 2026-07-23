<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneCPrice extends Model
{
    use HasFactory;

    protected $table = '1c_prices';

    protected $fillable = [
        'batch_id',
        'offer_external_id',
        'price_type',
        'price',
        'currency',
        'raw',
        'processed_at',
        'attempts',
        'error',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'raw' => 'array',
        'processed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function scopeUnprocessed($query)
    {
        return $query->whereNull('processed_at');
    }
}
