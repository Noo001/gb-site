<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramManagerSetting extends Model
{
    use HasFactory;

    protected $table = 'telegram_manager_settings';

    protected $fillable = [
        'key',
        'label',
        'value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function value(string $key, ?string $default = null): ?string
    {
        $setting = static::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        return $setting?->value ?? $default;
    }
}
