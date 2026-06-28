<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotKnowledge extends Model
{
    use HasFactory;

    protected $table = 'bot_knowledge';

    protected $fillable = [
        'type',
        'group',
        'key',
        'payload',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];
}
