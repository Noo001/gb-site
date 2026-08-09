<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneCStocksSnapshot extends Model
{
    use HasFactory;

    protected $table = 'one_c_stocks_snapshots';

    protected $fillable = [
        'batch_id',
        'offer_external_id',
        'store_external_id',
        'store_name',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];
}
