<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FranchiseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'city',
        'type',
        'budget',
        'message',
        'bitrix24_status',
    ];

    protected $casts = [
        'bitrix24_status' => 'boolean',
    ];
}
