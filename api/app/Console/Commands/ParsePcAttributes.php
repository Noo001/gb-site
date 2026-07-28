<?php

namespace App\Console\Commands;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Services\PcAttributeParser;
use Illuminate\Console\Command;

class ParsePcAttributes extends Command
{
    protected $signature = 'pc:parse-attributes {--dry-run : Показать, что будет записано, без изменений в БД}';

    protected $description = 'Парсит атрибуты комплектующих ПК (сокет, тип памяти, форм-фактор и т.д.) из названий товаров';

    /** Корневая категория «Комплектующие для ПК». */
    private const ROOT_CATEGORY_ID = 31;

    private const ROOT_CATEGORY_NAME = 'Комплектующие';

    public function handle(PcAttributeParser $parser): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $categoryIds = $this->componentCategoryIds();

        if (empty($categoryIds)) {
            $this->warn('Категории комплектующих не найдены.');

            return self::SUCCESS;
        }

        $categories = Category::query()->whereIn('id', $categoryIds)->pluck('name', 'id');

        $products = Product::query()
            ->whereIn('category_id', $categoryIds)
            ->orderBy('id')
            ->get();

        $this->info(sprintf('Товаров в категориях комплектующих: %d%s', $products->count(), $dryRun ? ' (dry-run)' : ''));

        $stats = [];
        $written = 0;
        $skipped = 0;

        foreach ($products as $product) {
            $attrs = $parser->parse($product, $categories[$product->category_id] ?? null);

            if (empty($attrs)) {
                $skipped++;
                $this->warn("Не удалось распарсить: [{$product->id}] {$product->name}");
                continue;
            }

            foreach ($attrs as $attr) {
                $stats[$attr['slug']] = ($stats[$attr['slug']] ?? 0) + 1;

                $this->line(sprintf('[%d] %s → %s = %s', $product->id, $product->name, $attr['slug'], $attr['value']));

                if ($dryRun) {
                    continue;
                }

                $attribute = Attribute::firstOrCreate(
                    ['slug' => $attr['slug']],
                    [
                        'name' => $attr['name'],
                        'type' => 'text',
                        'unit' => $attr['unit'],
                        'is_active' => true,
                        'is_filter' => true,
                    ]
                );

                ProductAttributeValue::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'attribute_id' => $attribute->id,
                        'offer_id' => null,
                    ],
                    ['value' => $attr['value']]
                );

                $written++;
            }
        }

        $this->newLine();
        $this->info($dryRun ? 'Статистика (dry-run, ничего не записано):' : 'Статистика:');

        foreach ($stats as $slug => $count) {
            $this->line("  {$slug}: {$count}");
        }

        $this->info("Записано значений: {$written}, пропущено товаров: {$skipped}");

        return self::SUCCESS;
    }

    /**
     * ID корневой категории комплектующих и всех её потомков (рекурсивно).
     *
     * @return array<int>
     */
    private function componentCategoryIds(): array
    {
        $root = Category::find(self::ROOT_CATEGORY_ID)
            ?? Category::query()->where('name', 'like', self::ROOT_CATEGORY_NAME . '%')->first();

        if (! $root) {
            return [];
        }

        $ids = [$root->id];
        $frontier = [$root->id];

        while (! empty($frontier)) {
            $children = Category::query()->whereIn('parent_id', $frontier)->pluck('id')->all();

            if (empty($children)) {
                break;
            }

            $ids = array_merge($ids, $children);
            $frontier = $children;
        }

        return $ids;
    }
}
