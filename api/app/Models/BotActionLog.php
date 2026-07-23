<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotActionLog extends Model
{
    use HasFactory;

    protected $table = 'bot_action_logs';

    protected $fillable = [
        'channel',
        'action',
        'payload',
        'metadata',
        'ip',
    ];

    protected $casts = [
        'payload' => 'array',
        'metadata' => 'array',
    ];
}
