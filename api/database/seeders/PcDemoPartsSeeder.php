<?php

namespace Database\Seeders;

use App\Models\PcDemoPart;
use Illuminate\Database\Seeder;

/**
 * Тестовые комплектующие для демо-режима конфигуратора ПК.
 *
 * Данные намеренно содержат пересечения и рассинхрон по сокетам,
 * типам памяти, форм-факторам и мощности БП, чтобы были видны
 * правила совместимости: LGA1700/AM5/AM4, DDR4/DDR5, ATX/mATX/Mini-ITX.
 */
class PcDemoPartsSeeder extends Seeder
{
    public function run(): void
    {
        PcDemoPart::query()->delete();

        $parts = [
            // Корпуса: form_factor — список допустимых форм-факторов материнских плат.
            ['slot' => 'case', 'name' => 'Корпус Zalman Z3 Mid Tower', 'price' => 5900, 'attributes' => ['form_factor' => 'ATX,mATX']],
            ['slot' => 'case', 'name' => 'Корпус AeroCool Mini Tower', 'price' => 4900, 'attributes' => ['form_factor' => 'mATX,Mini-ITX']],
            ['slot' => 'case', 'name' => 'Корпус Lian Li Full Tower', 'price' => 8900, 'attributes' => ['form_factor' => 'ATX,E-ATX,mATX']],

            // Процессоры.
            ['slot' => 'cpu', 'name' => 'Процессор Intel Core i5-13400F', 'price' => 19900, 'attributes' => ['socket' => 'LGA1700', 'tdp_w' => '65']],
            ['slot' => 'cpu', 'name' => 'Процессор Intel Core i7-14700K', 'price' => 44900, 'attributes' => ['socket' => 'LGA1700', 'tdp_w' => '125']],
            ['slot' => 'cpu', 'name' => 'Процессор AMD Ryzen 5 7600', 'price' => 22900, 'attributes' => ['socket' => 'AM5', 'tdp_w' => '105']],
            ['slot' => 'cpu', 'name' => 'Процессор AMD Ryzen 7 7800X3D', 'price' => 39900, 'attributes' => ['socket' => 'AM5', 'tdp_w' => '120']],

            // Материнские платы.
            ['slot' => 'motherboard', 'name' => 'Материнская плата ASUS PRIME B760M-K', 'price' => 12500, 'attributes' => ['socket' => 'LGA1700', 'memory_type' => 'DDR5', 'form_factor' => 'mATX']],
            ['slot' => 'motherboard', 'name' => 'Материнская плата MSI PRO B760M-A DDR4', 'price' => 11900, 'attributes' => ['socket' => 'LGA1700', 'memory_type' => 'DDR4', 'form_factor' => 'mATX']],
            ['slot' => 'motherboard', 'name' => 'Материнская плата Gigabyte B650 AORUS ELITE', 'price' => 18900, 'attributes' => ['socket' => 'AM5', 'memory_type' => 'DDR5', 'form_factor' => 'ATX']],
            ['slot' => 'motherboard', 'name' => 'Материнская плата ASRock B550M Pro4', 'price' => 9900, 'attributes' => ['socket' => 'AM4', 'memory_type' => 'DDR4', 'form_factor' => 'mATX']],

            // Видеокарты: psu_w — рекомендуемая мощность БП.
            ['slot' => 'gpu', 'name' => 'Видеокарта GeForce RTX 4060 8GB', 'price' => 38900, 'attributes' => ['gpu_chip' => 'RTX 4060', 'vram_gb' => '8', 'psu_w' => '550']],
            ['slot' => 'gpu', 'name' => 'Видеокарта GeForce RTX 4070 12GB', 'price' => 64900, 'attributes' => ['gpu_chip' => 'RTX 4070', 'vram_gb' => '12', 'psu_w' => '650']],
            ['slot' => 'gpu', 'name' => 'Видеокарта Radeon RX 7600 8GB', 'price' => 33900, 'attributes' => ['gpu_chip' => 'RX 7600', 'vram_gb' => '8', 'psu_w' => '550']],
            ['slot' => 'gpu', 'name' => 'Видеокарта GeForce RTX 3050 6GB', 'price' => 21900, 'attributes' => ['gpu_chip' => 'RTX 3050', 'vram_gb' => '6', 'psu_w' => '550']],

            // Оперативная память.
            ['slot' => 'ram', 'name' => 'Оперативная память Kingston Fury 16GB DDR5', 'price' => 5900, 'attributes' => ['memory_type' => 'DDR5', 'module_gb' => '16']],
            ['slot' => 'ram', 'name' => 'Оперативная память Corsair 32GB DDR5 (2×16)', 'price' => 11900, 'attributes' => ['memory_type' => 'DDR5', 'module_gb' => '32']],
            ['slot' => 'ram', 'name' => 'Оперативная память Kingston Fury 16GB DDR4', 'price' => 3900, 'attributes' => ['memory_type' => 'DDR4', 'module_gb' => '16']],
            ['slot' => 'ram', 'name' => 'Оперативная память Crucial 8GB DDR4', 'price' => 2400, 'attributes' => ['memory_type' => 'DDR4', 'module_gb' => '8']],

            // Накопители.
            ['slot' => 'storage', 'name' => 'SSD Kingston NV2 1TB NVMe', 'price' => 6900, 'attributes' => ['capacity_gb' => '1000']],
            ['slot' => 'storage', 'name' => 'SSD Samsung 980 500GB NVMe', 'price' => 4900, 'attributes' => ['capacity_gb' => '500']],
            ['slot' => 'storage', 'name' => 'HDD Seagate Barracuda 2TB', 'price' => 5900, 'attributes' => ['capacity_gb' => '2000']],

            // Блоки питания.
            ['slot' => 'psu', 'name' => 'Блок питания Chieftec 550W', 'price' => 4900, 'attributes' => ['wattage' => '550']],
            ['slot' => 'psu', 'name' => 'Блок питания Be Quiet 650W', 'price' => 6900, 'attributes' => ['wattage' => '650']],
            ['slot' => 'psu', 'name' => 'Блок питания Corsair RM750 750W', 'price' => 9900, 'attributes' => ['wattage' => '750']],
            ['slot' => 'psu', 'name' => 'Блок питания DeepCool 850W', 'price' => 11900, 'attributes' => ['wattage' => '850']],

            // Необязательное.
            ['slot' => 'extra', 'name' => 'Кулер башенный DeepCool AK400', 'price' => 2900, 'attributes' => []],
            ['slot' => 'extra', 'name' => 'Доп. вентиляторы ×3 Arctic P12', 'price' => 2400, 'attributes' => []],
        ];

        foreach ($parts as $index => $part) {
            PcDemoPart::create($part + ['stock' => 5, 'sort' => $index]);
        }
    }
}
