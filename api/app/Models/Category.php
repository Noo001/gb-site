<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * Служебные категории 1С, которые не должны быть видны в каталоге сайта.
     */
    public const SERVICE_NAMES = [
        'На удаление',
        'Для заказа',
        'Переклейка',
        'Б/У',
        'Витринные образцы',
        'Обменные устройства',
        'Внутренее',
        'Ремонт',
        'Подписки',
        'Модульная замена',
        'Номенклатуры для ремонта',
        'Компонентный ремонт / ПО / Чистки',
        'Подарочная упаковка',
        'Кресла и стулья',
        'Очки',
        'Игрушки',
        'Товары для ванной',
        'Товары для животных',
        'Товары для ухода за одеждой',
        'Климат / Климатические установки',
        'Безопасная сеть',
        'ВЫГОДНЫЕ ПРЕДЛОЖЕНИЯ',
        'Аскессуары для роботов-пылесосов',
    ];

    protected $fillable = [
        'parent_id',
        'external_id',
        'name',
        'slug',
        'full_path',
        'description',
        'url',
        'sort',
        'is_active',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_h1',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function getUrlAttribute($value): ?string
    {
        return $value ?: $this->full_path;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function seoMetadata()
    {
        return $this->morphOne(SeoMetadata::class, 'entity');
    }

    public function scopeForCatalog($query)
    {
        return $query->where('is_active', true)
            ->whereNotIn('name', self::SERVICE_NAMES);
    }

    public function isService(): bool
    {
        return in_array($this->name, self::SERVICE_NAMES, true);
    }
}
