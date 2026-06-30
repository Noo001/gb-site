<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneCProduct extends Model
{
    use HasFactory;

    protected $table = '1c_products';

    protected $fillable = [
        'batch_id',
        'external_id',
        'category_external_id',
        'name',
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
