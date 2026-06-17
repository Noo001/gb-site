<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoMetadata extends Model
{
    use HasFactory;

    protected $table = 'seo_metadata';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'url',
        'title',
        'description',
        'keywords',
        'h1',
        'og_title',
        'og_description',
        'og_image',
        'canonical',
        'robots',
        'json_ld',
    ];

    protected $casts = [
        'json_ld' => 'array',
    ];

    public function entity()
    {
        return $this->morphTo();
    }
}
