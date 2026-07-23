<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotProduct extends Model
{
    use HasFactory;

    protected $table = 'bot_products';

    public $timestamps = false;

    protected $fillable = [
        'offer_id',
        'product_id',
        'name',
        'brand',
        'category',
        'subcategory',
        'price',
        'old_price',
        'currency',
        'availability',
        'quantity',
        'url',
        'image_url',
        'available_in_cities',
        'city_availability',
        'metadata',
        'search_text',
        'is_active',
        'updated_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'available_in_cities' => 'array',
        'city_availability' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'updated_at' => 'datetime',
    ];
}
