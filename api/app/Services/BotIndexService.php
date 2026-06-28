<?php

namespace App\Services;

use App\Models\BotProduct;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;

class BotIndexService
{
    public function rebuild(): array
    {
        $started = now();
        $created = 0;

        DB::transaction(function () use (&$created) {
            BotProduct::query()->delete();

            Offer::query()
                ->with(['product.category', 'prices', 'stocks.store', 'attributeValues.attribute', 'product.media'])
                ->where('is_active', true)
                ->whereHas('product', function ($q) {
                    $q->where('is_active', true);
                })
                ->chunkById(200, function ($offers) use (&$created) {
                    foreach ($offers as $offer) {
                        $product = $offer->product;
                        if (! $product) {
                            continue;
                        }

                        $row = $this->buildRow($product, $offer);
                        BotProduct::create($row);
                        $created++;
                    }
                });
        });

        return [
            'created' => $created,
            'duration_ms' => (int) round(now()->diffInMilliseconds($started)),
        ];
    }

    private function buildRow($product, $offer): array
    {
        $category = $product->category;
        $categoryName = $category?->name;
        $subcategoryName = null;
        if ($category && $category->parent_id) {
            // Try to load parent name if already eager-loaded; otherwise leave category as current.
            $parentName = optional($category->parent)->name;
            if ($parentName) {
                $subcategoryName = $categoryName;
                $categoryName = $parentName;
            }
        }

        $prices = $offer->prices->where('region_id', null)->where('store_id', null);
        if ($prices->isEmpty()) {
            $prices = $offer->prices;
        }
        $price = $prices->min('price') ?? 0;
        $oldPrice = $prices->max('old_price');
        $currency = $prices->first()?->currency ?? 'RUB';

        $available = [];
        $cityAvailability = [];
        $totalQty = 0;
        foreach ($offer->stocks as $stock) {
            $qty = max((float) $stock->quantity - (float) $stock->reserved, 0);
            if ($qty <= 0 || ! $stock->store) {
                continue;
            }
            $totalQty += $qty;
            $city = $stock->store->city ?: 'Неизвестно';
            $available[$city] = true;
            $cityAvailability[$city] = [
                'available' => true,
                'quantity' => $qty,
                'store_id' => $stock->store_id,
            ];
        }

        $metadata = [];
        $searchParts = [
            $product->name,
            $offer->name,
            $product->brand,
            $product->sku,
            $offer->sku,
            $categoryName,
            $subcategoryName,
        ];

        foreach ($offer->attributeValues as $attrValue) {
            $attribute = $attrValue->attribute;
            if (! $attribute) {
                continue;
            }
            $value = $attrValue->value;
            $searchParts[] = $attribute->name;
            $searchParts[] = $value;

            $slug = mb_strtolower($attribute->slug);
            if (str_contains($slug, 'color')) {
                $metadata['color'] = $value;
            } elseif (str_contains($slug, 'storage') || str_contains($slug, 'memory')) {
                $metadata['storage'] = $value;
            } elseif (str_contains($slug, 'sim')) {
                $metadata['sim_type'] = $value;
            } elseif (str_contains($slug, 'ram')) {
                $metadata['ram_gb'] = is_numeric($value) ? (int) $value : $value;
            } elseif (str_contains($slug, 'cpu') || str_contains($slug, 'processor')) {
                $metadata['cpu'] = $value;
            }
        }

        $searchText = collect($searchParts)
            ->filter()
            ->unique()
            ->implode(' ');

        $imageUrl = null;
        if (method_exists($product, 'getFirstMediaUrl')) {
            $imageUrl = $product->getFirstMediaUrl('images');
        }

        return [
            'offer_id' => $offer->id,
            'product_id' => $product->id,
            'name' => $offer->name ?: $product->name,
            'brand' => $product->brand,
            'category' => $categoryName,
            'subcategory' => $subcategoryName,
            'price' => $price,
            'old_price' => $oldPrice,
            'currency' => $currency,
            'availability' => $totalQty > 0 ? 'in_stock' : 'out_of_stock',
            'quantity' => $totalQty,
            'url' => $product->url,
            'image_url' => $imageUrl ?: null,
            'available_in_cities' => array_values(array_unique(array_keys($available))),
            'city_availability' => $cityAvailability,
            'metadata' => $metadata,
            'search_text' => $searchText,
            'is_active' => true,
            'updated_at' => now(),
        ];
    }
}
