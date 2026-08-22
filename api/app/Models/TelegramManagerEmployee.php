<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramManagerEmployee extends Model
{
    use HasFactory;

    protected $table = 'telegram_manager_employees';

    protected $fillable = [
        'full_name',
        'telegram_chat_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
