<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    use HasFactory;

    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    protected $fillable = [
        'direction',
        'system',
        'endpoint',
        'method',
        'payload',
        'headers',
        'response',
        'status_code',
        'duration_ms',
        'ip',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'response' => 'array',
        'status_code' => 'integer',
        'duration_ms' => 'integer',
    ];
}
