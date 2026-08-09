<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotTriggerPhrase extends Model
{
    use HasFactory;

    protected $table = 'bot_trigger_phrases';

    protected $fillable = [
        'phrase',
        'action',
        'response',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];
}
