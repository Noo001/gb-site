<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneCOffer extends Model
{
    use HasFactory;

    protected $table = '1c_offers';

    protected $fillable = [
        'batch_id',
        'external_id',
        'product_external_id',
        'name',
        'sku',
        'barcode',
        'raw',
        'processed_at',
        'attempts',
        'error',
    ];

    protected $casts = [
        'raw' => 'array',
        'processed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function scopeUnprocessed($query)
    {
        return $query->whereNull('processed_at');
    }
}
