<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_STORE = 'store';
    public const TYPE_DEPARTMENT = 'department';
    public const TYPE_SERVICE = 'service';

    protected $fillable = [
        'external_id',
        'name',
        'type',
        'city',
        'address',
        'phone',
        'email',
        'schedule',
        'latitude',
        'longitude',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Store $store) {
            $store->type = self::resolveType($store->name);
        });
    }

    /**
     * Определяем тип склада по названию:
     * - service: РЦ, Брак, Уценка, Склад приема, placeholder-склады, тестовые.
     * - department: подразделения 1С.
     * - store: обычный магазин/склад.
     */
    public static function resolveType(?string $name): string
    {
        if ($name === null) {
            return self::TYPE_STORE;
        }

        $lower = mb_strtolower($name);

        $serviceMarkers = [
            'тест', 'test', '>>выберите склад<<', 'брак', 'уценка',
            'склад приема', 'склад приёма', 'рц ', 'распределительный центр',
        ];
        foreach ($serviceMarkers as $marker) {
            if (str_contains($lower, $marker)) {
                return self::TYPE_SERVICE;
            }
        }

        if (str_starts_with($lower, '(подразделение)')) {
            return self::TYPE_DEPARTMENT;
        }

        return self::TYPE_STORE;
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
