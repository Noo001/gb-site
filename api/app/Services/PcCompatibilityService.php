<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * Сервис совместимости комплектующих для конфигуратора ПК.
 *
 * Слоты сборки и правила подбора: сокет CPU/материнской платы,
 * тип памяти ОЗУ/материнской платы, форм-фактор корпуса, мощность БП.
 */
class PcCompatibilityService
{
    /**
     * Источники категорий по слотам:
     * ids — известные id категорий, names — подстроки для поиска по имени (на будущее,
     * когда категории появятся в 1С с произвольными id).
     */
    private const SLOT_CATEGORIES = [
        'case' => ['ids' => [], 'names' => ['Корпус']],
        'cpu' => ['ids' => [354], 'names' => ['Процессор']],
        'motherboard' => ['ids' => [355], 'names' => ['Материнск']],
        'gpu' => ['ids' => [353], 'names' => ['Видеокарт']],
        'ram' => ['ids' => [356], 'names' => ['ОЗУ', 'Оперативн']],
        'storage' => ['ids' => [], 'names' => ['SSD', 'Накопител', 'Жёстк', 'Жестк']],
        'psu' => ['ids' => [], 'names' => ['Блок питания', 'Блоки питания']],
        'extra' => ['ids' => [], 'names' => ['Кулер', 'Охлажден', 'Вентилятор']],
    ];

    private const SLOT_TITLES = [
        'case' => 'Корпус',
        'cpu' => 'Процессор',
        'motherboard' => 'Материнская плата',
        'gpu' => 'Видеокарта',
        'ram' => 'Память',
        'storage' => 'Накопитель',
        'psu' => 'Блок питания',
        'extra' => 'Необязательное',
    ];

    /** Запас мощности БП поверх TDP процессора и рекомендации видеокарты. */
    private const PSU_HEADROOM_W = 100;

    /** @var array<string, array<int>> */
    private array $categoryIdsCache = [];

    /**
     * Конфиг слотов конфигуратора по порядку сборки.
     *
     * @return array<int, array{id: string, title: string, required: bool}>
     */
    public function slots(): array
    {
        $slots = [];

        foreach (self::SLOT_TITLES as $id => $title) {
            $slots[] = [
                'id' => $id,
                'title' => $title,
                'required' => $id !== 'extra',
            ];
        }

        return $slots;
    }

    public function hasSlot(string $slot): bool
    {
        return isset(self::SLOT_TITLES[$slot]);
    }

    /**
     * Есть ли в базе вообще доступные товары для слота (без учёта сборки).
     */
    public function slotHasParts(string $slot): bool
    {
        $categoryIds = $this->categoryIdsForSlot($slot);

        if (empty($categoryIds)) {
            return false;
        }

        return $this->baseQuery($categoryIds)->exists();
    }

    /**
     * Доступные товары слота, отфильтрованные правилами совместимости
     * относительно уже выбранных комплектующих.
     *
     * @param array<string, int> $build Выбранные позиции: slot => product_id
     * @return Collection<int, Product>
     */
    public function availableParts(string $slot, array $build = []): Collection
    {
        $categoryIds = $this->categoryIdsForSlot($slot);

        if (empty($categoryIds)) {
            return new Collection();
        }

        $buildAttrs = $this->resolveBuildAttributes($build);

        return $this->baseQuery($categoryIds)
            ->with(['attributeValues.attribute', 'offers.prices', 'offers.stocks'])
            ->orderBy('name')
            ->get()
            ->filter(fn (Product $product) => $this->isCompatible($slot, $this->resolveAttributes($product), $buildAttrs))
            ->values();
    }

    /**
     * Атрибуты товара в виде [slug => value].
     *
     * @return array<string, string>
     */
    public function resolveAttributes(Product $product): array
    {
        $values = $product->relationLoaded('attributeValues')
            ? $product->attributeValues
            : $product->attributeValues()->with('attribute')->get();

        $attributes = [];

        foreach ($values as $value) {
            $attribute = $value->relationLoaded('attribute')
                ? $value->attribute
                : $value->attribute()->first();

            if ($attribute) {
                $attributes[$attribute->slug] = $value->value;
            }
        }

        return $attributes;
    }

    /**
     * ID категорий слота: известные id + найденные по имени.
     *
     * @return array<int>
     */
    public function categoryIdsForSlot(string $slot): array
    {
        if (isset($this->categoryIdsCache[$slot])) {
            return $this->categoryIdsCache[$slot];
        }

        $config = self::SLOT_CATEGORIES[$slot] ?? ['ids' => [], 'names' => []];
        $ids = collect($config['ids']);

        foreach ($config['names'] as $name) {
            $ids = $ids->merge(
                Category::query()->where('name', 'like', '%' . $name . '%')->pluck('id')
            );
        }

        return $this->categoryIdsCache[$slot] = $ids->unique()->values()->all();
    }

    private function baseQuery(array $categoryIds)
    {
        return Product::query()
            ->where('is_active', true)
            ->whereIn('category_id', $categoryIds)
            ->whereHas('offers', function ($query) {
                $query->whereHas('prices', fn ($q) => $q->where('price', '>', 0))
                    ->whereHas('stocks', fn ($q) => $q->where('quantity', '>', 0));
            });
    }

    /**
     * @param array<string, int> $build
     * @return array<string, array<string, string>>
     */
    private function resolveBuildAttributes(array $build): array
    {
        $ids = array_filter(array_values($build));

        if (empty($ids)) {
            return [];
        }

        $products = Product::query()
            ->with('attributeValues.attribute')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $resolved = [];

        foreach ($build as $slot => $productId) {
            $product = $products->get($productId);
            if ($product) {
                $resolved[$slot] = $this->resolveAttributes($product);
            }
        }

        return $resolved;
    }

    /**
     * Проверка совместимости кандидата слота с уже выбранными комплектующими.
     * Если у одной из сторон нет нужного атрибута — считаем совместимой
     * (не отсекаем то, что не смогли распарсить).
     *
     * @param array<string, string> $candidate
     * @param array<string, array<string, string>> $buildAttrs
     */
    private function isCompatible(string $slot, array $candidate, array $buildAttrs): bool
    {
        // Сокет: процессор <-> материнская плата.
        if ($slot === 'motherboard' && isset($buildAttrs['cpu'])) {
            if (! $this->matches($candidate['socket'] ?? null, $buildAttrs['cpu']['socket'] ?? null)) {
                return false;
            }
        }
        if ($slot === 'cpu' && isset($buildAttrs['motherboard'])) {
            if (! $this->matches($candidate['socket'] ?? null, $buildAttrs['motherboard']['socket'] ?? null)) {
                return false;
            }
        }

        // Тип памяти: ОЗУ <-> материнская плата.
        if ($slot === 'ram' && isset($buildAttrs['motherboard'])) {
            if (! $this->matches($candidate['memory_type'] ?? null, $buildAttrs['motherboard']['memory_type'] ?? null)) {
                return false;
            }
        }
        if ($slot === 'motherboard' && isset($buildAttrs['ram'])) {
            if (! $this->matches($candidate['memory_type'] ?? null, $buildAttrs['ram']['memory_type'] ?? null)) {
                return false;
            }
        }

        // Форм-фактор: корпус <-> материнская плата.
        if ($slot === 'motherboard' && isset($buildAttrs['case'])) {
            if (! $this->caseFits($buildAttrs['case']['form_factor'] ?? null, $candidate['form_factor'] ?? null)) {
                return false;
            }
        }
        if ($slot === 'case' && isset($buildAttrs['motherboard'])) {
            if (! $this->caseFits($candidate['form_factor'] ?? null, $buildAttrs['motherboard']['form_factor'] ?? null)) {
                return false;
            }
        }

        // Мощность БП: >= TDP процессора + рекомендация видеокарты + запас.
        if ($slot === 'psu') {
            $required = $this->requiredPsuWattage($buildAttrs);
            $wattage = isset($candidate['wattage']) ? (int) $candidate['wattage'] : null;

            if ($required !== null && $wattage !== null && $wattage < $required) {
                return false;
            }
        }

        return true;
    }

    /**
     * Оба значения известны — должны совпадать; иначе считаем совместимыми.
     */
    private function matches(?string $a, ?string $b): bool
    {
        if ($a === null || $b === null) {
            return true;
        }

        return mb_strtoupper($a) === mb_strtoupper($b);
    }

    /**
     * Влезает ли форм-фактор материнской платы в корпус.
     * form_factor корпуса — список допустимых форм-факторов через запятую.
     */
    private function caseFits(?string $caseFormFactors, ?string $mbFormFactor): bool
    {
        if ($caseFormFactors === null || $mbFormFactor === null) {
            return true;
        }

        $allowed = array_map('trim', explode(',', $caseFormFactors));

        return in_array($mbFormFactor, $allowed, true);
    }

    /**
     * Требуемая мощность БП по выбранным CPU и GPU.
     * null, если ни процессор, ни видеокарта не выбраны.
     *
     * @param array<string, array<string, string>> $buildAttrs
     */
    private function requiredPsuWattage(array $buildAttrs): ?int
    {
        $cpuTdp = isset($buildAttrs['cpu']['tdp_w']) ? (int) $buildAttrs['cpu']['tdp_w'] : null;
        $gpuPsu = isset($buildAttrs['gpu']['psu_w']) ? (int) $buildAttrs['gpu']['psu_w'] : null;

        if ($cpuTdp === null && $gpuPsu === null) {
            return null;
        }

        return ($cpuTdp ?? 0) + ($gpuPsu ?? 0) + self::PSU_HEADROOM_W;
    }
}
