<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneCPrice extends Model
{
    use HasFactory;

    protected $table = '1c_prices';

    protected $fillable = [
        'offer_external_id',
        'price_type',
        'price',
        'currency',
        'raw',
        'processed_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'raw' => 'array',
        'processed_at' => 'datetime',
    ];
}
