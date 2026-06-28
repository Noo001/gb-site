<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyPriceChangedTo1C;
use App\Jobs\Sync1CProductImages;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Price;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OneCController extends Controller
{
    public function syncProducts(Request $request): JsonResponse
    {
        $data = $request->validate([
            'uuid_1c' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:1000'],
            'article' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'images_urls' => ['nullable', 'array'],
            'images_urls.*' => ['url'],
        ]);

        $product = Price::withoutSyncNotifications(function () use ($data) {
            return DB::transaction(function () use ($data) {
                $product = Product::firstOrNew(['uuid_1c' => $data['uuid_1c']]);
                $isNew = ! $product->exists;

                $categoryId = null;
                if (! empty($data['category'])) {
                    $categoryId = $this->findOrCreateCategory($data['category'])?->id;
                }

                $product->fill([
                    'name' => $data['name'],
                    'category_id' => $categoryId,
                    'sku' => $data['article'] ?? $product->sku,
                    'description' => $data['description'] ?? $product->description,
                    'is_active' => $data['is_active'] ?? true,
                ]);

                if ($isNew) {
                    $product->slug = $this->uniqueSlug($data['name']);
                    $product->url = '/product/' . $product->slug;
                }

                $product->save();

                $offer = $this->ensureDefaultOffer($product, $data['article'] ?? null);
                $this->updatePrice($offer, (float) $data['price'], $data['currency'] ?? 'RUB');

                if (isset($data['quantity'])) {
                    $this->updateStock($offer, (float) $data['quantity']);
                }

                if (! empty($data['images_urls'])) {
                    Sync1CProductImages::dispatch($product->id, $data['images_urls']);
                }

                return $product;
            });
        });

        $action = $product->wasRecentlyCreated ? 'created' : 'updated';

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'action' => $action,
        ]);
    }

    public function syncPrices(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.uuid_1c' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.currency' => ['nullable', 'string', 'max:3'],
            'items.*.date_from' => ['nullable', 'date_format:Y-m-d\TH:i:sP'],
        ]);

        $updated = 0;
        $failed = 0;
        $errors = [];

        Price::withoutSyncNotifications(function () use ($data, &$updated, &$failed, &$errors) {
            DB::transaction(function () use ($data, &$updated, &$failed, &$errors) {
                foreach ($data['items'] as $index => $item) {
                    $product = Product::where('uuid_1c', $item['uuid_1c'])->first();

                    if (! $product) {
                        $failed++;
                        $errors[] = [
                            'index' => $index,
                            'uuid_1c' => $item['uuid_1c'],
                            'error' => 'Товар не найден.',
                        ];
                        continue;
                    }

                    $offer = $this->ensureDefaultOffer($product, null);
                    $this->updatePrice($offer, (float) $item['price'], $item['currency'] ?? 'RUB');
                    $updated++;
                }
            });
        });

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors,
        ]);
    }

    public function listProducts(Request $request): JsonResponse
    {
        $data = $request->validate([
            'updated_since' => ['nullable', 'date_format:Y-m-d\TH:i:sP'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'offset' => ['nullable', 'integer', 'min:0'],
        ]);

        $query = Product::query()
            ->whereNotNull('uuid_1c')
            ->orderBy('updated_at');

        if (! empty($data['updated_since'])) {
            $query->where('updated_at', '>=', Carbon::parse($data['updated_since']));
        }

        $limit = $data['limit'] ?? 100;
        $offset = $data['offset'] ?? 0;

        $products = $query->limit($limit)->offset($offset)->get();

        return response()->json([
            'success' => true,
            'meta' => [
                'limit' => $limit,
                'offset' => $offset,
                'count' => $products->count(),
            ],
            'data' => $products->map(fn (Product $p) => $this->productResource($p)),
        ]);
    }

    public function showProduct(string $uuid): JsonResponse
    {
        $product = Product::where('uuid_1c', $uuid)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->productResource($product, true),
        ]);
    }

    public function notifyPriceChanged(Request $request): JsonResponse
    {
        $data = $request->validate([
            'uuid_1c' => ['required', 'string', 'max:255'],
            'new_price' => ['required', 'numeric', 'min:0'],
            'changed_at' => ['required', 'date_format:Y-m-d\TH:i:sP'],
            'source' => ['required', 'string', 'in:admin_panel'],
        ]);

        $product = Product::where('uuid_1c', $data['uuid_1c'])->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'uuid_1c' => ['Товар с указанным uuid_1c не найден.'],
            ]);
        }

        NotifyPriceChangedTo1C::dispatch($product->uuid_1c, (float) $data['new_price'], $data['changed_at'], $data['source']);

        return response()->json([
            'success' => true,
            'message' => 'Задача на уведомление 1С поставлена в очередь.',
        ]);
    }

    private function findOrCreateCategory(string $name): Category
    {
        $category = Category::where('name', $name)->first();

        if ($category) {
            return $category;
        }

        return Category::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'is_active' => true,
        ]);
    }

    private function ensureDefaultOffer(Product $product, ?string $sku): Offer
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
            'name' => $product->name,
            'sku' => $sku,
            'is_active' => true,
        ]);
    }

    private function updatePrice(Offer $offer, float $price, string $currency): void
    {
        Price::updateOrCreate(
            [
                'offer_id' => $offer->id,
                'region_id' => null,
                'store_id' => null,
            ],
            [
                'price' => $price,
                'currency' => strtoupper($currency),
            ]
        );
    }

    private function updateStock(Offer $offer, float $quantity): void
    {
        $storeId = Store::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->value('id');

        Stock::updateOrCreate(
            [
                'offer_id' => $offer->id,
                'store_id' => $storeId,
            ],
            [
                'quantity' => $quantity,
                'reserved' => 0,
            ]
        );
    }

    private function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name) ?: 'tovar';
        $slug = $base;
        $counter = 1;

        while (Product::where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()
            || Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function productResource(Product $product, bool $full = false): array
    {
        $price = $product->currentPrice();
        $stock = $product->currentStock();

        $data = [
            'uuid_1c' => $product->uuid_1c,
            'name' => $product->name,
            'price' => $price ? (float) $price->price : null,
            'currency' => $price?->currency,
            'quantity' => $stock ? (float) $stock->quantity : null,
            'updated_at' => $product->updated_at?->toIso8601String(),
        ];

        if ($full) {
            $data['article'] = $product->sku;
            $data['description'] = $product->description;
            $data['category'] = $product->category?->name;
            $data['is_active'] = $product->is_active;
        }

        return $data;
    }
}
