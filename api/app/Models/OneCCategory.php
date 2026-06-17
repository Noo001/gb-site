<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneCCategory extends Model
{
    use HasFactory;

    protected $table = '1c_categories';

    protected $fillable = [
        'external_id',
        'parent_external_id',
        'name',
        'raw',
        'processed_at',
    ];

    protected $casts = [
        'raw' => 'array',
        'processed_at' => 'datetime',
    ];
}
