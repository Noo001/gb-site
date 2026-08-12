<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouletteSector extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'type',
        'value',
        'cost_bonus',
        'probability_weight',
        'is_active',
        'sort',
        'metadata',
    ];

    protected $casts = [
        'value' => 'integer',
        'cost_bonus' => 'integer',
        'probability_weight' => 'integer',
        'is_active' => 'boolean',
        'sort' => 'integer',
        'metadata' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort');
    }
}
