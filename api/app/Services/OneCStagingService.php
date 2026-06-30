<?php

namespace App\Services;

use App\Models\OneCCategory;
use App\Models\OneCOffer;
use App\Models\OneCPrice;
use App\Models\OneCProduct;
use App\Models\OneCStock;
use Illuminate\Support\Str;

class OneCStagingService
{
    public function __construct(
        private OneCSyncService $syncService
    ) {
    }

    public function store(array $data): string
    {
        $batchId = (string) Str::uuid();

        $this->storeCategories($batchId, $data['categories'] ?? []);
        $this->storeProducts($batchId, $data['products'] ?? []);

        return $batchId;
    }

    public function stageAndApplyProduct(array $data): array
    {
        $batchId = (string) Str::uuid();

        $records = $this->storeSingleProduct($batchId, $data);

        return $this->syncService->apply($records);
    }

    public function stageAndApplyCategory(array $data): array
    {
        $batchId = (string) Str::uuid();

        $staging = $this->storeSingleCategory($batchId, $data);

        return $this->syncService->apply([$staging]);
    }

    public function stageAndApplyPrice(array $data): array
    {
        $batchId = (string) Str::uuid();

        $staging = $this->storeSinglePrice($batchId, $data);

        return $this->syncService->apply([$staging]);
    }

    public function stageAndApplyStock(array $data): array
    {
        $batchId = (string) Str::uuid();

        $staging = $this->storeSingleStock($batchId, $data);

        return $this->syncService->apply([$staging]);
    }

    public function storeCategories(string $batchId, array $categories): void
    {
        foreach ($categories as $category) {
            $this->storeSingleCategory($batchId, $category);
        }
    }

    public function storeProducts(string $batchId, array $products): void
    {
        foreach ($products as $product) {
            $this->storeSingleProduct($batchId, $product);
        }
    }

    private function storeSingleCategory(string $batchId, array $category): OneCCategory
    {
        return OneCCategory::updateOrCreate(
            [
                'batch_id' => $batchId,
                'external_id' => $category['external_id'],
            ],
            [
                'parent_external_id' => $category['parent_external_id'] ?? null,
                'name' => $category['name'],
                'raw' => [
                    'is_active' => $category['is_active'] ?? true,
                    'sort' => $category['sort'] ?? 0,
                ],
            ]
        );
    }

    private function storeSingleProduct(string $batchId, array $product): array
    {
        $records = [];

        $records[] = OneCProduct::updateOrCreate(
            [
                'batch_id' => $batchId,
                'external_id' => $product['external_id'],
            ],
            [
                'category_external_id' => $product['category_external_id'] ?? null,
                'name' => $product['name'],
                'raw' => [
                    'sku' => $product['sku'] ?? null,
                    'brand' => $product['brand'] ?? null,
                    'description' => $product['description'] ?? null,
                    'is_active' => $product['is_active'] ?? true,
                    'images_urls' => $product['images_urls'] ?? [],
                    'attributes' => $product['attributes'] ?? [],
                ],
            ]
        );

        if (! empty($product['offers'])) {
            foreach ($product['offers'] as $offer) {
                $records[] = $this->storeSingleOffer($batchId, array_merge($offer, [
                    'product_external_id' => $product['external_id'],
                ]));

                foreach ($offer['prices'] ?? [] as $price) {
                    $records[] = $this->storeSinglePrice($batchId, array_merge($price, [
                        'offer_external_id' => $offer['external_id'],
                    ]));
                }

                foreach ($offer['stocks'] ?? [] as $stock) {
                    $records[] = $this->storeSingleStock($batchId, array_merge($stock, [
                        'offer_external_id' => $offer['external_id'],
                    ]));
                }
            }
        } else {
            $records[] = $this->storeSingleOffer($batchId, [
                'external_id' => $product['external_id'],
                'product_external_id' => $product['external_id'],
                'name' => $product['name'],
                'sku' => $product['sku'] ?? null,
                'barcode' => null,
                'is_active' => true,
            ]);

            if (! empty($product['price'])) {
                $records[] = $this->storeSinglePrice($batchId, [
                    'offer_external_id' => $product['external_id'],
                    'price_type' => $product['price_type'] ?? null,
                    'price' => $product['price'],
                    'currency' => $product['currency'] ?? 'RUB',
                ]);
            }

            if (isset($product['quantity'])) {
                $records[] = $this->storeSingleStock($batchId, [
                    'offer_external_id' => $product['external_id'],
                    'store_external_id' => $product['store_external_id'] ?? null,
                    'quantity' => $product['quantity'],
                ]);
            }
        }

        return $records;
    }

    private function storeOffers(string $batchId, string $productExternalId, array $offers): void
    {
        foreach ($offers as $offer) {
            $this->storeSingleOffer($batchId, array_merge($offer, [
                'product_external_id' => $productExternalId,
            ]));

            $this->storePrices($batchId, $offer['external_id'], $offer['prices'] ?? []);
            $this->storeStocks($batchId, $offer['external_id'], $offer['stocks'] ?? []);
        }
    }

    private function storeSingleOffer(string $batchId, array $offer): OneCOffer
    {
        return OneCOffer::updateOrCreate(
            [
                'batch_id' => $batchId,
                'external_id' => $offer['external_id'],
            ],
            [
                'product_external_id' => $offer['product_external_id'],
                'name' => $offer['name'],
                'sku' => $offer['sku'] ?? null,
                'barcode' => $offer['barcode'] ?? null,
                'raw' => [
                    'is_active' => $offer['is_active'] ?? true,
                ],
            ]
        );
    }

    private function storePrices(string $batchId, string $offerExternalId, array $prices): void
    {
        foreach ($prices as $price) {
            $this->storeSinglePrice($batchId, array_merge($price, [
                'offer_external_id' => $offerExternalId,
            ]));
        }
    }

    private function storeSinglePrice(string $batchId, array $price): OneCPrice
    {
        return OneCPrice::updateOrCreate(
            [
                'batch_id' => $batchId,
                'offer_external_id' => $price['offer_external_id'],
                'price_type' => $price['price_type'] ?? null,
            ],
            [
                'price' => $price['price'],
                'currency' => $price['currency'] ?? 'RUB',
                'raw' => [],
            ]
        );
    }

    private function storeStocks(string $batchId, string $offerExternalId, array $stocks): void
    {
        foreach ($stocks as $stock) {
            $this->storeSingleStock($batchId, array_merge($stock, [
                'offer_external_id' => $offerExternalId,
            ]));
        }
    }

    private function storeSingleStock(string $batchId, array $stock): OneCStock
    {
        return OneCStock::updateOrCreate(
            [
                'batch_id' => $batchId,
                'offer_external_id' => $stock['offer_external_id'],
                'store_external_id' => $stock['store_external_id'] ?? null,
            ],
            [
                'quantity' => $stock['quantity'],
                'raw' => [],
            ]
        );
    }

    public function getStatistics(string $batchId): array
    {
        return [
            'categories' => OneCCategory::where('batch_id', $batchId)->count(),
            'products' => OneCProduct::where('batch_id', $batchId)->count(),
            'offers' => OneCOffer::where('batch_id', $batchId)->count(),
            'prices' => OneCPrice::where('batch_id', $batchId)->count(),
            'stocks' => OneCStock::where('batch_id', $batchId)->count(),
            'unprocessed' => [
                'categories' => OneCCategory::where('batch_id', $batchId)->unprocessed()->count(),
                'products' => OneCProduct::where('batch_id', $batchId)->unprocessed()->count(),
                'offers' => OneCOffer::where('batch_id', $batchId)->unprocessed()->count(),
                'prices' => OneCPrice::where('batch_id', $batchId)->unprocessed()->count(),
                'stocks' => OneCStock::where('batch_id', $batchId)->unprocessed()->count(),
            ],
            'errors' => [
                'categories' => OneCCategory::where('batch_id', $batchId)->whereNotNull('error')->count(),
                'products' => OneCProduct::where('batch_id', $batchId)->whereNotNull('error')->count(),
                'offers' => OneCOffer::where('batch_id', $batchId)->whereNotNull('error')->count(),
                'prices' => OneCPrice::where('batch_id', $batchId)->whereNotNull('error')->count(),
                'stocks' => OneCStock::where('batch_id', $batchId)->whereNotNull('error')->count(),
            ],
        ];
    }
}
