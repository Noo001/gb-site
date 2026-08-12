<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouletteSpin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sector_id',
        'is_free',
        'cost_bonus',
        'status',
        'result_payload',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'cost_bonus' => 'integer',
        'result_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(RouletteSector::class);
    }
}
