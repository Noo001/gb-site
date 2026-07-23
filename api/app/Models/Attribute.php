<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'external_id',
        'name',
        'slug',
        'type',
        'unit',
        'sort',
        'is_active',
        'is_filter',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_filter' => 'boolean',
        'sort' => 'integer',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}
