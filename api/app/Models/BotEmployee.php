<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotEmployee extends Model
{
    use HasFactory;

    protected $table = 'bot_employees';

    protected $fillable = [
        'full_name',
        'b24_token',
        'department',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];
}
