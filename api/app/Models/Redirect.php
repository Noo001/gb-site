<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_url',
        'to_url',
        'status_code',
        'hits',
        'is_active',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'hits' => 'integer',
        'is_active' => 'boolean',
    ];
}
