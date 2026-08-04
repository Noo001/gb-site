<?php

namespace App\Services;

use App\Models\PcDemoPart;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Сервис автоподбора конфигурации ПК по бюджету.
 *
 * Использует правила совместимости из PcCompatibilityService и жадный
 * алгоритм распределения бюджета по слотам. Цель (purpose) влияет на
 * доли бюджета, отдаваемые процессору, видеокарте, памяти и т.д.
 */
class PcAutoBuildService
{
    private PcCompatibilityService $compatibility;

    /** Порядок подбора обязательных слотов. */
    private const BUILD_ORDER = ['cpu', 'motherboard', 'gpu', 'ram', 'storage', 'case', 'cooler', 'psu'];

    /** Доли бюджета по слотам для разных сценариев использования. */
    private const SHARES = [
        'games' => [
            'cpu' => 0.15,
            'motherboard' => 0.12,
            'gpu' => 0.33,
            'ram' => 0.10,
            'storage' => 0.10,
            'case' => 0.07,
            'cooler' => 0.05,
            'psu' => 0.08,
        ],
        'work' => [
            'cpu' => 0.20,
            'motherboard' => 0.12,
            'gpu' => 0.18,
            'ram' => 0.14,
            'storage' => 0.12,
            'case' => 0.08,
            'cooler' => 0.06,
            'psu' => 0.10,
        ],
        'office' => [
            'cpu' => 0.18,
            'motherboard' => 0.12,
            'gpu' => 0.10,
            'ram' => 0.15,
            'storage' => 0.12,
            'case' => 0.12,
            'cooler' => 0.09,
            'psu' => 0.12,
        ],
        'other' => [
            'cpu' => 0.18,
            'motherboard' => 0.12,
            'gpu' => 0.20,
            'ram' => 0.13,
            'storage' => 0.13,
            'case' => 0.10,
            'cooler' => 0.06,
            'psu' => 0.08,
        ],
    ];

    /** Слоты, для которых выбираем самый дешёвый подходящий вариант (не самый дорогой). */
    private const CHEAPEST_SLOTS = ['cooler', 'psu'];

    public function __construct(PcCompatibilityService $compatibility)
    {
        $this->compatibility = $compatibility;
    }

    /**
     * Попытаться собрать конфигурацию в рамках бюджета.
     *
     * @param float|int $budget
     * @param string|null $purpose games|work|office|other
     * @return array|null Массив ['items' => [slot => [...]], 'total' => ...] или null
     */
    public function build($budget, ?string $purpose = null): ?array
    {
        $budget = (float) $budget;

        if ($budget <= 0) {
            return null;
        }

        $shares = self::SHARES[$purpose] ?? self::SHARES['other'];
        $remaining = $budget;
        $selected = [];
        $buildIds = [];

        foreach (self::BUILD_ORDER as $slot) {
            $share = $shares[$slot] ?? 0;
            $slotLimit = min($remaining, $budget * $share);

            if ($this->compatibility->isDemoMode()) {
                $candidates = $this->compatibility->availableDemoParts($slot, $buildIds);
            } else {
                $candidates = $this->compatibility->availableParts($slot, $buildIds);
            }

            if ($candidates->isEmpty()) {
                return null;
            }

            $candidate = $this->chooseCandidate($slot, $candidates, $slotLimit, $remaining);

            if ($candidate === null) {
                return null;
            }

            $buildIds[$slot] = $candidate['id'];
            $selected[$slot] = $candidate;
            $remaining -= $candidate['price'];

            if ($remaining < 0) {
                return null;
            }
        }

        $total = $budget - $remaining;

        if ($total > $budget) {
            return null;
        }

        $this->optimizeBuild($selected, $buildIds, $remaining, $budget);
        $total = $budget - $remaining;

        return [
            'items' => $selected,
            'total' => $total,
        ];
    }

    /**
     * Довести сборку ближе к бюджету, поднимая отдельные комплектующие
     * до более дорогих, но совместимых вариантов, пока остаётся бюджет.
     */
    private function optimizeBuild(array &$selected, array &$buildIds, float &$remaining, float $budget): void
    {
        $minGain = max(1000.0, $budget * 0.01);
        $maxIterations = 50;

        for ($i = 0; $i < $maxIterations && $remaining >= $minGain; $i++) {
            $upgrades = [];

            foreach (self::BUILD_ORDER as $slot) {
                $current = $selected[$slot];
                $currentPrice = (float) $current['price'];

                $candidates = $this->compatibility->isDemoMode()
                    ? $this->compatibility->availableDemoParts($slot, $buildIds)
                    : $this->compatibility->availableParts($slot, $buildIds);

                $formatted = $candidates->map(fn ($item) => $this->formatCandidate($slot, $item));
                $better = $formatted
                    ->filter(fn ($item) => $item['price'] !== null)
                    ->filter(fn ($item) => $item['price'] > $currentPrice + 0.001)
                    ->filter(fn ($item) => $item['price'] <= $currentPrice + $remaining + 0.001);

                foreach ($better as $candidate) {
                    $upgrades[] = [
                        'slot' => $slot,
                        'candidate' => $candidate,
                        'gain' => (float) $candidate['price'] - $currentPrice,
                    ];
                }
            }

            if (empty($upgrades)) {
                break;
            }

            usort($upgrades, fn ($a, $b) => $b['gain'] <=> $a['gain']);

            $applied = false;

            foreach ($upgrades as $upgrade) {
                $slot = $upgrade['slot'];
                $oldCandidate = $selected[$slot];
                $oldBuildId = $buildIds[$slot];

                $selected[$slot] = $upgrade['candidate'];
                $buildIds[$slot] = $upgrade['candidate']['id'];
                $remaining -= $upgrade['gain'];

                if ($this->isBuildValid($selected, $buildIds, $budget)) {
                    $applied = true;
                    break;
                }

                // Откат, если замена нарушила совместимость других слотов.
                $selected[$slot] = $oldCandidate;
                $buildIds[$slot] = $oldBuildId;
                $remaining += $upgrade['gain'];
            }

            if (! $applied) {
                break;
            }
        }
    }

    /**
     * Проверить, что каждый выбранный компонент всё ещё доступен
     * с учётом текущей сборки (совместимость и цена <= бюджет).
     */
    private function isBuildValid(array $selected, array $buildIds, float $budget): bool
    {
        foreach (self::BUILD_ORDER as $slot) {
            $candidates = $this->compatibility->isDemoMode()
                ? $this->compatibility->availableDemoParts($slot, $buildIds)
                : $this->compatibility->availableParts($slot, $buildIds);

            $ids = $candidates->map(fn ($item) => $item->id)->all();

            if (! in_array($selected[$slot]['id'], $ids, true)) {
                return false;
            }
        }

        $total = array_sum(array_map(fn ($item) => (float) $item['price'], $selected));

        return $total <= $budget + 0.001;
    }

    /**
     * Выбрать одну позицию из кандидатов.
     *
     * Сначала пытаемся уложиться в лимит слота (долю бюджета). Если не
     * получается — берём любого, кто влезает в оставшийся бюджет.
     * Для кулера и БП предпочитаем самый дешёвый подходящий вариант.
     */
    private function chooseCandidate(string $slot, Collection $candidates, float $slotLimit, float $remaining): ?array
    {
        $formatted = $candidates->map(fn ($item) => $this->formatCandidate($slot, $item));

        // Исключаем позиции без цены в реальном режиме.
        $withPrice = $formatted->filter(fn ($item) => $item['price'] !== null);

        if ($withPrice->isEmpty()) {
            return null;
        }

        // Сначала ищем в пределах лимита слота.
        $withinLimit = $withPrice->filter(fn ($item) => $item['price'] <= $slotLimit + 0.001);

        $pool = $withinLimit->isNotEmpty() ? $withinLimit : $withPrice->filter(fn ($item) => $item['price'] <= $remaining + 0.001);

        if ($pool->isEmpty()) {
            return null;
        }

        if (in_array($slot, self::CHEAPEST_SLOTS, true)) {
            return $pool->sortBy('price')->first();
        }

        return $pool->sortByDesc('price')->first();
    }

    private function formatCandidate(string $slot, $item): array
    {
        if ($item instanceof Product) {
            $price = $item->currentPrice();

            return [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $price ? (float) $price->price : null,
                'stock' => $item->offers->flatMap(fn ($offer) => $offer->stocks)->sum(fn ($stock) => (float) $stock->quantity),
                'attributes' => $this->compatibility->resolveAttributes($item),
            ];
        }

        return [
            'id' => $item->id,
            'name' => $item->name,
            'price' => (float) $item->price,
            'stock' => $item->stock,
            'attributes' => $this->compatibility->resolveDemoAttributes($item),
        ];
    }
}
