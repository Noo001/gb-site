<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneCStock extends Model
{
    use HasFactory;

    protected $table = '1c_stocks';

    protected $fillable = [
        'offer_external_id',
        'store_external_id',
        'quantity',
        'raw',
        'processed_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'raw' => 'array',
        'processed_at' => 'datetime',
    ];
}
