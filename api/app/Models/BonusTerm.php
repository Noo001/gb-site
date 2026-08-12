<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function current(): ?self
    {
        return static::query()->where('is_active', true)->latest('id')->first();
    }
}
