<?php

namespace App\Services;

use App\Models\Product;

/**
 * Парсер атрибутов комплектующих ПК из названий товаров.
 *
 * Возвращает массив вида:
 *   [['slug' => 'socket', 'name' => 'Сокет', 'unit' => null, 'value' => 'LGA1700'], ...]
 */
class PcAttributeParser
{
    /** Рекомендуемая мощность БП (Вт) по графическому чипу. */
    private const GPU_PSU_W = [
        'RTX 3050' => 550,
        'RTX 4060' => 550,
        'RTX 4060 TI' => 600,
        'RTX 4070' => 650,
        'RTX 4070 TI' => 750,
        'RTX 4080' => 850,
        'RTX 4090' => 1000,
        'RX 7600' => 550,
        'RX 7800 XT' => 700,
    ];

    private const GPU_PSU_W_DEFAULT = 600;

    /** TDP (Вт) по линейке процессора. */
    private const CPU_TDP_W = [
        'i3' => 65,
        'i5' => 65,
        'i7' => 65,
        'i9' => 125,
        'ryzen 3' => 65,
        'ryzen 5' => 65,
        'ryzen 7' => 105,
        'ryzen 9' => 105,
    ];

    /** Допустимые форм-факторы материнских плат по типу корпуса. */
    private const CASE_FORM_FACTORS = [
        'full tower' => 'E-ATX,ATX,mATX,Mini-ITX',
        'mid tower' => 'ATX,mATX,Mini-ITX',
        'mini tower' => 'mATX,Mini-ITX',
    ];

    /** Определения атрибутов: slug => [название, единица]. */
    public const ATTRIBUTE_DEFINITIONS = [
        'socket' => ['Сокет', null],
        'memory_type' => ['Тип памяти', null],
        'form_factor' => ['Форм-фактор', null],
        'gpu_chip' => ['Графический чип', null],
        'vram_gb' => ['Объём видеопамяти', 'ГБ'],
        'psu_w' => ['Рекомендуемая мощность БП', 'Вт'],
        'module_gb' => ['Объём модуля', 'ГБ'],
        'tdp_w' => ['TDP', 'Вт'],
        'wattage' => ['Мощность', 'Вт'],
    ];

    /**
     * Распарсить атрибуты товара по его названию и категории.
     *
     * @return array<int, array{slug: string, name: string, unit: ?string, value: string}>
     */
    public function parse(Product $product, ?string $categoryName = null): array
    {
        $type = $this->detectType($product->name, $categoryName);

        if ($type === null) {
            return [];
        }

        $name = $product->name;
        $attrs = [];

        $add = function (string $slug, string|int|null $value) use (&$attrs) {
            if ($value === null || $value === '') {
                return;
            }
            [$attrName, $unit] = self::ATTRIBUTE_DEFINITIONS[$slug];
            $attrs[$slug] = [
                'slug' => $slug,
                'name' => $attrName,
                'unit' => $unit,
                'value' => (string) $value,
            ];
        };

        switch ($type) {
            case 'motherboard':
                $add('socket', $this->parseSocket($name));
                $add('memory_type', $this->parseMemoryType($name));
                $add('form_factor', $this->parseMbFormFactor($name));
                break;

            case 'gpu':
                $chip = $this->parseGpuChip($name);
                $add('gpu_chip', $chip);
                $add('vram_gb', $this->parseGb($name));
                if ($chip !== null) {
                    $add('psu_w', self::GPU_PSU_W[mb_strtoupper($chip)] ?? self::GPU_PSU_W_DEFAULT);
                }
                break;

            case 'ram':
                $add('memory_type', $this->parseMemoryType($name));
                $add('module_gb', $this->parseGb($name));
                break;

            case 'cpu':
                $add('socket', $this->parseSocket($name));
                $add('tdp_w', $this->parseCpuTdp($name));
                break;

            case 'case':
                $add('form_factor', $this->parseCaseFormFactor($name));
                break;

            case 'psu':
                $add('wattage', $this->parseWattage($name));
                break;
        }

        return array_values($attrs);
    }

    /**
     * Определить тип комплектующего по названию категории или товара.
     */
    public function detectType(string $productName, ?string $categoryName = null): ?string
    {
        foreach ([$categoryName, $productName] as $subject) {
            if ($subject === null) {
                continue;
            }

            $type = match (true) {
                (bool) preg_match('/материнск/iu', $subject) => 'motherboard',
                (bool) preg_match('/видеокарт/iu', $subject) => 'gpu',
                (bool) preg_match('/\bОЗУ\b|оперативн|модул[ья] памяти/iu', $subject) => 'ram',
                (bool) preg_match('/процессор/iu', $subject) => 'cpu',
                (bool) preg_match('/корпус/iu', $subject) => 'case',
                (bool) preg_match('/блок[аи]? питания|блоки питания/iu', $subject) => 'psu',
                default => null,
            };

            if ($type !== null) {
                return $type;
            }
        }

        return null;
    }

    private function parseSocket(string $name): ?string
    {
        if (preg_match('/\b(LGA\s?1700|LGA\s?1200|LGA\s?1851|AM4|AM5)\b/i', $name, $m)) {
            return mb_strtoupper(preg_replace('/\s+/', '', $m[1]));
        }

        return null;
    }

    private function parseMemoryType(string $name): ?string
    {
        if (preg_match('/\b(DDR[45])\b/i', $name, $m)) {
            return mb_strtoupper($m[1]);
        }

        return null;
    }

    private function parseMbFormFactor(string $name): ?string
    {
        if (! preg_match('/\b(E-ATX|Mini-ITX|Micro-ATX|mATX|mITX|ATX)\b/i', $name, $m)) {
            return null;
        }

        return match (mb_strtolower($m[1])) {
            'e-atx' => 'E-ATX',
            'mini-itx', 'mitx' => 'Mini-ITX',
            'micro-atx', 'matx' => 'mATX',
            default => 'ATX',
        };
    }

    private function parseGpuChip(string $name): ?string
    {
        if (! preg_match('/\b((?:RTX|GTX|RX)\s?\d{3,4}(?:\s?(?:Ti|Super|XT|XTX))?)\b/i', $name, $m)) {
            return null;
        }

        $chip = mb_strtoupper(preg_replace('/\s+/', ' ', trim($m[1])));
        $chip = str_replace([' TI', ' SUPER'], [' Ti', ' Super'], $chip);

        return $chip;
    }

    private function parseGb(string $name): ?int
    {
        if (preg_match('/(\d{1,3})\s?(?:GB|ГБ)\b/iu', $name, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function parseCpuTdp(string $name): ?int
    {
        if (preg_match('/\b(i[3579])-?\d/i', $name, $m)) {
            return self::CPU_TDP_W[mb_strtolower($m[1])] ?? null;
        }

        if (preg_match('/\bRyzen\s?([3579])\b/i', $name, $m)) {
            return self::CPU_TDP_W['ryzen ' . $m[1]] ?? null;
        }

        return null;
    }

    private function parseCaseFormFactor(string $name): ?string
    {
        if (preg_match('/\b(Full\s?Tower|Mid\s?Tower|Mini\s?Tower)\b/i', $name, $m)) {
            $key = mb_strtolower(preg_replace('/\s+/', ' ', $m[1]));

            return self::CASE_FORM_FACTORS[$key] ?? null;
        }

        return null;
    }

    private function parseWattage(string $name): ?int
    {
        if (preg_match('/(\d{3,4})\s?(?:W|Вт)\b/iu', $name, $m)) {
            return (int) $m[1];
        }

        return null;
    }
}
