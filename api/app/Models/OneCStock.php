<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneCStock extends Model
{
    use HasFactory;

    protected $table = '1c_stocks';

    protected $fillable = [
        'batch_id',
        'offer_external_id',
        'store_external_id',
        'quantity',
        'raw',
        'processed_at',
        'attempts',
        'error',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'raw' => 'array',
        'processed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function scopeUnprocessed($query)
    {
        return $query->whereNull('processed_at');
    }
}
