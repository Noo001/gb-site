<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcDemoPart extends Model
{
    protected $fillable = ['slot', 'name', 'price', 'stock', 'attributes', 'sort'];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'attributes' => 'array',
        'sort' => 'integer',
    ];
}
