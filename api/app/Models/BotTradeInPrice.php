<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotTradeInPrice extends Model
{
    use HasFactory;

    protected $table = 'bot_trade_in_prices';

    protected $fillable = [
        'brand',
        'model',
        'storage',
        'condition',
        'price',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
