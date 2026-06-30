<?php

namespace App\Services;

use App\Jobs\Sync1CProductImages;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Offer;
use App\Models\OneCCategory;
use App\Models\OneCOffer;
use App\Models\OneCPrice;
use App\Models\OneCProduct;
use App\Models\OneCStock;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Stock;
use App\Models\Store;
use App\Services\BotIndexService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class OneCSyncService
{
    public function __construct(
        private BotIndexService $botIndexService
    ) {
    }

    /**
     * @param array<int, OneCCategory|OneCProduct|OneCOffer|OneCPrice|OneCStock> $records
     */
    public function apply(array $records): array
    {
        return Price::withoutSyncNotifications(function () use ($records) {
            return DB::transaction(function () use ($records) {
                $result = [
                    'processed' => 0,
                    'failed' => 0,
                    'errors' => [],
                ];

                foreach ($records as $record) {
                    try {
                        $record->increment('attempts');

                        match (true) {
                            $record instanceof OneCCategory => $this->applyCategory($record),
                            $record instanceof OneCProduct => $this->applyProduct($record),
                            $record instanceof OneCOffer => $this->applyOffer($record),
                            $record instanceof OneCPrice => $this->applyPrice($record),
                            $record instanceof OneCStock => $this->applyStock($record),
                            default => throw new \RuntimeException('Unknown staging record type'),
                        };

                        $this->markProcessed($record);
                        $result['processed']++;
                    } catch (Throwable $e) {
                        $this->markFailed($record, $e);
                        $result['failed']++;
                        $result['errors'][] = [
                            'type' => class_basename($record),
                            'external_id' => $record->external_id ?? $record->offer_external_id ?? null,
                            'error' => $e->getMessage(),
                        ];
                    }
                }

                $this->rebuildCategoryPaths();

                return $result;
            });
        });
    }

    public function applyCategory(OneCCategory $staging): Category
    {
        $parentId = null;
        if ($staging->parent_external_id) {
            $parentId = Category::where('external_id', $staging->parent_external_id)->value('id');
        }

        $category = Category::updateOrCreate(
            ['external_id' => $staging->external_id],
            [
                'parent_id' => $parentId,
                'name' => $staging->name,
                'slug' => $this->uniqueCategorySlug($staging->name, $staging->external_id),
                'is_active' => $staging->raw['is_active'] ?? true,
                'sort' => $staging->raw['sort'] ?? 0,
            ]
        );

        return $category;
    }

    public function applyProduct(OneCProduct $staging): Product
    {
        $categoryId = null;
        if ($staging->category_external_id) {
            $categoryId = Category::where('external_id', $staging->category_external_id)->value('id');
        }

        $product = Product::firstOrNew(['uuid_1c' => $staging->external_id]);
        $isNew = ! $product->exists;

        $product->fill([
            'name' => $staging->name,
            'category_id' => $categoryId,
            'sku' => $staging->raw['sku'] ?? $product->sku,
            'brand' => $staging->raw['brand'] ?? $product->brand,
            'description' => $staging->raw['description'] ?? $product->description,
            'is_active' => $staging->raw['is_active'] ?? true,
        ]);

        if ($isNew) {
            $product->slug = $this->uniqueProductSlug($staging->name);
            $product->url = '/product/' . $product->slug;
        }

        $product->save();

        if (! empty($staging->raw['images_urls'])) {
            Sync1CProductImages::dispatch($product->id, $staging->raw['images_urls']);
        }

        $this->applyProductAttributes($product, $staging->raw['attributes'] ?? []);

        $offer = $this->ensureDefaultOfferForProduct($product);
        $this->botIndexService->upsertFromOffer($offer);

        return $product;
    }

    public function applyOffer(OneCOffer $staging): Offer
    {
        $product = Product::where('uuid_1c', $staging->product_external_id)->first();

        if (! $product) {
            throw new \RuntimeException("Product {$staging->product_external_id} not found");
        }

        $offer = Offer::updateOrCreate(
            ['external_id' => $staging->external_id],
            [
                'product_id' => $product->id,
                'name' => $staging->name,
                'sku' => $staging->sku,
                'barcode' => $staging->barcode,
                'is_active' => $staging->raw['is_active'] ?? true,
            ]
        );

        $this->botIndexService->upsertFromOffer($offer);

        return $offer;
    }

    public function applyPrice(OneCPrice $staging): Price
    {
        $offer = Offer::where('external_id', $staging->offer_external_id)->first();

        if (! $offer) {
            throw new \RuntimeException("Offer {$staging->offer_external_id} not found");
        }

        $price = Price::updateOrCreate(
            [
                'offer_id' => $offer->id,
                'region_id' => null,
                'store_id' => null,
            ],
            [
                'price' => $staging->price,
                'currency' => strtoupper($staging->currency),
            ]
        );

        $this->botIndexService->upsertByOfferId($offer->id);

        return $price;
    }

    public function applyStock(OneCStock $staging): Stock
    {
        $offer = Offer::where('external_id', $staging->offer_external_id)->first();

        if (! $offer) {
            throw new \RuntimeException("Offer {$staging->offer_external_id} not found");
        }

        $storeId = null;
        if ($staging->store_external_id) {
            $store = Store::firstOrCreate(
                ['external_id' => $staging->store_external_id],
                ['name' => $staging->store_external_id, 'is_active' => true, 'sort' => 0]
            );
            $storeId = $store->id;
        }

        $stock = Stock::updateOrCreate(
            [
                'offer_id' => $offer->id,
                'store_id' => $storeId,
            ],
            [
                'quantity' => $staging->quantity,
                'reserved' => 0,
            ]
        );

        $this->botIndexService->upsertByOfferId($offer->id);

        return $stock;
    }

    public function ensureDefaultOfferForProduct(Product $product, ?string $sku = null, ?string $name = null): Offer
    {
        $offer = $product->defaultOffer();

        if ($offer) {
            if ($sku && ! $offer->sku) {
                $offer->update(['sku' => $sku]);
            }
            return $offer;
        }

        return Offer::create([
            'product_id' => $product->id,
            'external_id' => $product->uuid_1c,
            'name' => $name ?? $product->name,
            'sku' => $sku,
            'is_active' => true,
        ]);
    }

    public function applyProductAttributes(Product $product, array $attributes): void
    {
        foreach ($attributes as $attrData) {
            $attribute = Attribute::firstOrCreate(
                ['name' => $attrData['name']],
                [
                    'slug' => Str::slug($attrData['name']),
                    'type' => 'text',
                    'unit' => $attrData['unit'] ?? null,
                    'is_active' => true,
                ]
            );

            ProductAttributeValue::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'attribute_id' => $attribute->id,
                    'offer_id' => null,
                ],
                ['value' => $attrData['value']]
            );
        }
    }

    public function applyStore(string $externalId, ?array $data = null): Store
    {
        return Store::firstOrCreate(
            ['external_id' => $externalId],
            [
                'name' => $data['name'] ?? $externalId,
                'city' => $data['city'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'sort' => $data['sort'] ?? 0,
            ]
        );
    }

    public function deactivateProduct(string $externalId, bool $permanent = false): ?Product
    {
        $product = Product::where('uuid_1c', $externalId)->first();

        if (! $product) {
            return null;
        }

        $offerIds = $product->offers()->pluck('id')->all();

        if ($permanent) {
            $product->offers()->update(['is_active' => false]);
            $product->delete();
        } else {
            $product->update(['is_active' => false]);
            $product->offers()->update(['is_active' => false]);
        }

        foreach ($offerIds as $offerId) {
            $this->botIndexService->deactivateByOfferId($offerId);
        }

        return $product;
    }

    private function rebuildCategoryPaths(): void
    {
        $categories = Category::whereNotNull('external_id')->get();

        foreach ($categories as $category) {
            $path = $this->buildCategoryPath($category, $categories);
            if ($path && $category->full_path !== $path) {
                $category->update(['full_path' => $path]);
            }
        }
    }

    private function buildCategoryPath(Category $category, $categories): ?string
    {
        $slugs = [];
        $current = $category;
        $visited = [];

        while ($current) {
            if (isset($visited[$current->id])) {
                return null;
            }
            $visited[$current->id] = true;
            $slugs[] = $current->slug;
            $current = $categories->firstWhere('id', $current->parent_id);
        }

        if (empty($slugs)) {
            return null;
        }

        return '/catalog/' . implode('/', array_reverse($slugs)) . '/';
    }

    private function uniqueProductSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name) ?: 'tovar';
        $slug = $base;
        $counter = 1;

        while (Product::where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists()
            || Category::where('slug', $slug)->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function uniqueCategorySlug(string $name, string $externalId): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $counter = 1;

        while (Category::where('slug', $slug)
            ->where('external_id', '!=', $externalId)
            ->exists()
            || Product::where('slug', $slug)->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function markProcessed(Model $staging): void
    {
        $staging->update([
            'processed_at' => Carbon::now(),
            'error' => null,
        ]);
    }

    private function markFailed(Model $staging, Throwable $e): void
    {
        $staging->update([
            'error' => $e->getMessage(),
        ]);
    }
}
