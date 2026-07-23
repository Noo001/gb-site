<?php

namespace App\Console\Commands;

use App\Models\OneCCategory;
use App\Models\OneCOffer;
use App\Models\OneCPrice;
use App\Models\OneCProduct;
use App\Models\OneCStock;
use App\Services\OneCSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

class Import1CCommerceML extends Command
{
    protected $signature = '1c:import-commerceml
                            {path : Путь к папке с XML-файлами CommerceML}
                            {--output= : Сохранить JSON в файл вместо применения}
                            {--apply : Сразу применить данные через OneCSyncService}
                            {--chunk=100 : Размер пачки для применения}';

    protected $description = 'Конвертация выгрузки 1С CommerceML 3.1 в JSON и импорт в каталог.';

    private array $categories = [];
    private array $products = [];
    private array $offers = [];
    private array $storages = [];

    public function handle(OneCSyncService $syncService): int
    {
        $path = $this->argument('path');

        if (! File::isDirectory($path)) {
            $this->error("Папка не найдена: {$path}");
            return self::FAILURE;
        }

        $files = $this->collectFiles($path);

        if (empty($files)) {
            $this->error('XML-файлы не найдены.');
            return self::FAILURE;
        }

        $this->info('Найдено файлов: ' . count($files));

        $this->parseGroups($files['groups'] ?? []);
        $this->parseStorages($files['storages'] ?? []);
        $this->parseGoods($files['goods'] ?? []);
        $this->parseOffers($files['offers'] ?? []);
        $this->parsePrices($files['prices'] ?? []);
        $this->parseRests($files['rests'] ?? []);

        $data = $this->buildBulkData();

        $this->info('Категорий: ' . count($data['categories']));
        $this->info('Товаров: ' . count($data['products']));
        $this->info('Складов: ' . count($this->storages));

        if ($this->option('output')) {
            $outputPath = $this->option('output');
            File::put($outputPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->info('JSON сохранён: ' . $outputPath);
            return self::SUCCESS;
        }

        if ($this->option('apply')) {
            return $this->applyData($syncService, $data);
        }

        $this->warn('Данные не применены. Используй --output или --apply.');
        return self::SUCCESS;
    }

    private function collectFiles(string $path): array
    {
        $files = [];
        foreach (File::files($path) as $file) {
            $name = $file->getFilename();
            foreach (['groups', 'storages', 'goods', 'offers', 'prices', 'rests', 'propertiesGoods', 'propertiesOffers', 'priceLists', 'units'] as $type) {
                if (str_starts_with($name, $type . '_')) {
                    $files[$type][] = $file->getPathname();
                    break;
                }
            }
        }
        foreach ($files as &$list) {
            sort($list);
        }
        return $files;
    }

    private function parseGroups(array $files): void
    {
        foreach ($files as $file) {
            $xml = $this->loadXml($file);
            $this->walkGroups($xml->Классификатор->Группы ?? null);
        }
    }

    private function walkGroups(?SimpleXMLElement $node, ?string $parentId = null): void
    {
        if (! $node) {
            return;
        }

        foreach ($node->Группа ?? [] as $group) {
            $id = (string) $group->Ид;
            $name = (string) $group->Наименование;
            $this->categories[$id] = [
                'external_id' => $id,
                'parent_external_id' => $parentId,
                'name' => $name,
                'is_active' => ((string) $group->ПометкаУдаления) !== 'true',
                'sort' => 0,
            ];

            $this->walkGroups($group->Группы ?? null, $id);
        }
    }

    private function parseStorages(array $files): void
    {
        foreach ($files as $file) {
            $xml = $this->loadXml($file);
            foreach ($xml->Классификатор->Склады->Склад ?? [] as $storage) {
                $id = (string) $storage->Ид;
                $name = (string) $storage->Наименование;
                $parts = array_map('trim', explode(',', $name, 2));
                $this->storages[$id] = [
                    'external_id' => $id,
                    'name' => $name,
                    'city' => $parts[0] ?? null,
                    'address' => $parts[1] ?? null,
                    'is_active' => true,
                    'sort' => 0,
                ];
            }
        }
    }

    private function parseGoods(array $files): void
    {
        foreach ($files as $file) {
            $xml = $this->loadXml($file);
            foreach ($xml->Каталог->Товары->Товар ?? [] as $item) {
                $id = (string) $item->Ид;
                $categoryId = null;
                foreach ($item->Группы->Ид ?? [] as $groupId) {
                    $categoryId = (string) $groupId;
                    break;
                }

                $brand = '';
                if ($item->Изготовитель && $item->Изготовитель->Наименование) {
                    $brand = (string) $item->Изготовитель->Наименование;
                }

                $attributes = [];
                foreach ($item->ЗначенияРеквизитов->ЗначениеРеквизита ?? [] as $req) {
                    $attributes[] = [
                        'name' => (string) $req->Наименование,
                        'value' => (string) $req->Значение,
                    ];
                }

                $this->products[$id] = [
                    'external_id' => $id,
                    'category_external_id' => $categoryId,
                    'name' => (string) $item->Наименование,
                    'sku' => (string) $item->Артикул ?: null,
                    'brand' => $brand ?: null,
                    'description' => $this->cleanDescription((string) $item->Описание),
                    'is_active' => ((string) $item->ПометкаУдаления) !== 'true',
                    'attributes' => $attributes,
                    'offers' => [],
                ];
            }
        }
    }

    private function parseOffers(array $files): void
    {
        foreach ($files as $file) {
            $xml = $this->loadXml($file);
            foreach ($xml->ПакетПредложений->Предложения->Предложение ?? [] as $item) {
                $compositeId = (string) $item->Ид;
                [$productId, $offerId] = $this->splitCompositeId($compositeId);

                if (! $offerId) {
                    $offerId = $productId;
                }

                $attributes = [];
                foreach ($item->ХарактеристикиТовара->ХарактеристикаТовара ?? [] as $char) {
                    $value = (string) $char->Значение;
                    if ($value !== '') {
                        $attributes[] = [
                            'name' => (string) $char->Наименование,
                            'value' => $value,
                        ];
                    }
                }

                $this->offers[$offerId] = [
                    'external_id' => $offerId,
                    'product_external_id' => $productId,
                    'name' => (string) $item->Наименование,
                    'sku' => (string) $item->Артикул ?: null,
                    'barcode' => null,
                    'is_active' => ((string) $item->ПометкаУдаления) !== 'true',
                    'attributes' => $attributes,
                    'prices' => [],
                    'stocks' => [],
                ];
            }
        }
    }

    private function parsePrices(array $files): void
    {
        foreach ($files as $file) {
            $xml = $this->loadXml($file);
            foreach ($xml->ПакетПредложений->Предложения->Предложение ?? [] as $item) {
                $compositeId = (string) $item->Ид;
                [, $offerId] = $this->splitCompositeId($compositeId);

                if (! $offerId || ! isset($this->offers[$offerId])) {
                    continue;
                }

                foreach ($item->Цены->Цена ?? [] as $price) {
                    $this->offers[$offerId]['prices'][] = [
                        'price_type' => (string) ($price->ИдТипаЦены ?? 'retail'),
                        'price' => (float) $price->ЦенаЗаЕдиницу,
                        'currency' => (string) ($price->Валюта ?? 'RUB'),
                    ];
                }
            }
        }
    }

    private function parseRests(array $files): void
    {
        foreach ($files as $file) {
            $xml = $this->loadXml($file);
            foreach ($xml->ПакетПредложений->Предложения->Предложение ?? [] as $item) {
                $compositeId = (string) $item->Ид;
                [, $offerId] = $this->splitCompositeId($compositeId);

                if (! $offerId || ! isset($this->offers[$offerId])) {
                    continue;
                }

                foreach ($item->Остатки->Остаток ?? [] as $rest) {
                    $storeId = (string) ($rest->Склад->Ид ?? '');
                    $quantity = (float) ($rest->Склад->Количество ?? 0);

                    if (! $storeId || $storeId === '00000000-0000-0000-0000-000000000000') {
                        continue;
                    }

                    $this->offers[$offerId]['stocks'][] = [
                        'store_external_id' => $storeId,
                        'quantity' => $quantity,
                    ];
                }
            }
        }
    }

    private function buildBulkData(): array
    {
        foreach ($this->offers as $offer) {
            $productId = $offer['product_external_id'];
            if (! isset($this->products[$productId])) {
                continue;
            }

            unset($offer['product_external_id']);
            $this->products[$productId]['offers'][] = $offer;
        }

        return [
            'categories' => array_values($this->categories),
            'products' => array_values($this->products),
            'stores' => array_values($this->storages),
        ];
    }

    private function applyData(OneCSyncService $syncService, array $data): int
    {
        $chunkSize = (int) $this->option('chunk');
        $products = $data['products'];

        $this->applyCategories($syncService, $data['categories']);
        $this->applyStores($data['stores']);

        $total = count($products);
        $processed = 0;
        $failed = 0;

        foreach (array_chunk($products, $chunkSize) as $chunk) {
            $records = [];
            $batchId = (string) Str::uuid();

            foreach ($chunk as $product) {
                $records = array_merge($records, $this->createStagingRecords($batchId, $product));
            }

            $result = $syncService->apply($records);
            $processed += $result['processed'];
            $failed += $result['failed'];

            foreach ($result['errors'] as $error) {
                $this->error("[{$error['type']} {$error['external_id']}] {$error['error']}");
            }

            $this->info("Пачка: обработано {$result['processed']}, ошибок {$result['failed']}");
        }

        $this->info("Итого: обработано {$processed} из {$total}, ошибок {$failed}");
        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function applyCategories(OneCSyncService $syncService, array $categories): void
    {
        $records = [];
        $batchId = (string) Str::uuid();
        foreach ($categories as $category) {
            $records[] = OneCCategory::updateOrCreate(
                ['batch_id' => $batchId, 'external_id' => $category['external_id']],
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
        $result = $syncService->apply($records);
        $this->info("Категории: обработано {$result['processed']}, ошибок {$result['failed']}");
    }

    private function applyStores(array $stores): void
    {
        foreach ($stores as $store) {
            \App\Models\Store::firstOrCreate(
                ['external_id' => $store['external_id']],
                [
                    'name' => $store['name'],
                    'city' => $store['city'],
                    'address' => $store['address'],
                    'is_active' => true,
                    'sort' => 0,
                ]
            );
        }
        $this->info('Склады созданы/обновлены: ' . count($stores));
    }

    private function createStagingRecords(string $batchId, array $product): array
    {
        $records = [];

        $records[] = OneCProduct::updateOrCreate(
            ['batch_id' => $batchId, 'external_id' => $product['external_id']],
            [
                'category_external_id' => $product['category_external_id'] ?? null,
                'name' => $product['name'],
                'raw' => [
                    'sku' => $product['sku'] ?? null,
                    'brand' => $product['brand'] ?? null,
                    'description' => $product['description'] ?? null,
                    'is_active' => $product['is_active'] ?? true,
                    'attributes' => $product['attributes'] ?? [],
                ],
            ]
        );

        foreach ($product['offers'] as $offer) {
            $records[] = OneCOffer::updateOrCreate(
                ['batch_id' => $batchId, 'external_id' => $offer['external_id']],
                [
                    'product_external_id' => $product['external_id'],
                    'name' => $offer['name'],
                    'sku' => $offer['sku'] ?? null,
                    'barcode' => $offer['barcode'] ?? null,
                    'raw' => ['is_active' => $offer['is_active'] ?? true],
                ]
            );

            foreach ($offer['prices'] as $price) {
                $records[] = OneCPrice::updateOrCreate(
                    ['batch_id' => $batchId, 'offer_external_id' => $offer['external_id'], 'price_type' => $price['price_type'] ?? null],
                    [
                        'price' => $price['price'],
                        'currency' => $price['currency'] ?? 'RUB',
                        'raw' => [],
                    ]
                );
            }

            foreach ($offer['stocks'] as $stock) {
                $records[] = OneCStock::updateOrCreate(
                    ['batch_id' => $batchId, 'offer_external_id' => $offer['external_id'], 'store_external_id' => $stock['store_external_id']],
                    [
                        'quantity' => $stock['quantity'],
                        'raw' => [],
                    ]
                );
            }
        }

        return $records;
    }

    private function loadXml(string $path): SimpleXMLElement
    {
        $content = file_get_contents($path);
        return new SimpleXMLElement($content);
    }

    private function splitCompositeId(string $id): array
    {
        $parts = explode('#', $id, 2);
        return [$parts[0], $parts[1] ?? null];
    }

    private function cleanDescription(?string $html): ?string
    {
        if (! $html) {
            return null;
        }

        $text = html_entity_decode(strip_tags($html));
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text) ?: null;
    }
}
