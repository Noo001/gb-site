<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Order;
use App\Models\PcDemoPart;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Setting;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PcConfiguratorTest extends TestCase
{
    use RefreshDatabase;

    private Category $componentsRoot;
    private Category $motherboards;
    private Category $ram;
    private Category $gpus;
    private Category $cpus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->componentsRoot = Category::create(['name' => 'Комплектующие для ПК', 'slug' => 'komplektuyushchie-pk']);
        $this->motherboards = Category::create(['name' => 'Материнские платы', 'slug' => 'mb', 'parent_id' => $this->componentsRoot->id]);
        $this->ram = Category::create(['name' => 'ОЗУ', 'slug' => 'ram', 'parent_id' => $this->componentsRoot->id]);
        $this->gpus = Category::create(['name' => 'Видеокарты', 'slug' => 'gpu', 'parent_id' => $this->componentsRoot->id]);
        $this->cpus = Category::create(['name' => 'Процессоры', 'slug' => 'cpu', 'parent_id' => $this->componentsRoot->id]);

        // Web-запросы в тестах идут с фиксированным CSRF-токеном.
        $this->withSession(['_token' => 'test-csrf-token']);
        $this->withHeaders(['X-CSRF-TOKEN' => 'test-csrf-token']);
    }

    public function test_parse_attributes_command_extracts_attributes_from_names(): void
    {
        $mb = $this->makeProduct($this->motherboards, 'Материнская плата ASUS PRIME B760M-K DDR5 LGA1700 mATX');
        $ram = $this->makeProduct($this->ram, 'Оперативная память Kingston Fury 16GB DDR5 5600');
        $gpu = $this->makeProduct($this->gpus, 'Видеокарта ASUS GeForce RTX 4060 Ti 8GB');

        $this->artisan('pc:parse-attributes')->assertSuccessful();

        $this->assertSame('LGA1700', $this->attrValue($mb, 'socket'));
        $this->assertSame('DDR5', $this->attrValue($mb, 'memory_type'));
        $this->assertSame('mATX', $this->attrValue($mb, 'form_factor'));

        $this->assertSame('DDR5', $this->attrValue($ram, 'memory_type'));
        $this->assertSame('16', $this->attrValue($ram, 'module_gb'));

        $this->assertSame('RTX 4060 Ti', $this->attrValue($gpu, 'gpu_chip'));
        $this->assertSame('8', $this->attrValue($gpu, 'vram_gb'));
        $this->assertSame('600', $this->attrValue($gpu, 'psu_w'));
    }

    public function test_parse_attributes_dry_run_writes_nothing(): void
    {
        $this->makeProduct($this->motherboards, 'Материнская плата ASUS PRIME B760M-K DDR5 LGA1700 mATX');

        $this->artisan('pc:parse-attributes', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(0, ProductAttributeValue::count());
    }

    public function test_parts_filters_ram_by_motherboard_memory_type(): void
    {
        $mb = $this->makeProduct($this->motherboards, 'Материнская плата DDR5 LGA1700');
        $this->setAttr($mb, 'memory_type', 'DDR5');

        $ramDdr4 = $this->makeProduct($this->ram, 'Оперативная память DDR4 16GB');
        $this->setAttr($ramDdr4, 'memory_type', 'DDR4');

        $ramDdr5 = $this->makeProduct($this->ram, 'Оперативная память DDR5 16GB');
        $this->setAttr($ramDdr5, 'memory_type', 'DDR5');

        $response = $this->getJson('/api/pc/parts?' . http_build_query([
            'slot' => 'ram',
            'build' => json_encode(['motherboard' => $mb->id]),
        ]));

        $response->assertOk()->assertJsonPath('empty', false);

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertContains($ramDdr5->id, $ids);
        $this->assertNotContains($ramDdr4->id, $ids);
    }

    public function test_parts_filters_motherboard_by_cpu_socket(): void
    {
        $cpu = $this->makeProduct($this->cpus, 'Процессор AMD Ryzen 7 7800X3D AM5');
        $this->setAttr($cpu, 'socket', 'AM5');

        $mbLga = $this->makeProduct($this->motherboards, 'Материнская плата LGA1700 DDR5');
        $this->setAttr($mbLga, 'socket', 'LGA1700');

        $mbAm5 = $this->makeProduct($this->motherboards, 'Материнская плата AM5 DDR5');
        $this->setAttr($mbAm5, 'socket', 'AM5');

        $response = $this->getJson('/api/pc/parts?' . http_build_query([
            'slot' => 'motherboard',
            'build' => json_encode(['cpu' => $cpu->id]),
        ]));

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertContains($mbAm5->id, $ids);
        $this->assertNotContains($mbLga->id, $ids);
    }

    public function test_parts_returns_empty_flag_for_slot_without_products(): void
    {
        $response = $this->getJson('/api/pc/parts?slot=psu');

        $response->assertOk()
            ->assertJsonPath('empty', true)
            ->assertJsonPath('data', []);
    }

    public function test_slots_returns_configurator_steps(): void
    {
        $this->makeProduct($this->gpus, 'Видеокарта ASUS GeForce RTX 4060 8GB');

        $response = $this->getJson('/api/pc/slots');

        $response->assertOk();

        $slots = collect($response->json('data'));

        $this->assertSame(
            ['case', 'cpu', 'cooler', 'motherboard', 'gpu', 'ram', 'storage', 'psu', 'extra'],
            $slots->pluck('id')->all()
        );

        $gpuSlot = $slots->firstWhere('id', 'gpu');
        $this->assertFalse($gpuSlot['empty']);
        $this->assertTrue($gpuSlot['required']);

        $extraSlot = $slots->firstWhere('id', 'extra');
        $this->assertFalse($extraSlot['required']);
        $this->assertTrue($extraSlot['empty']);
    }

    public function test_build_store_creates_order_with_items_and_total(): void
    {
        $mb = $this->makeProduct($this->motherboards, 'Материнская плата ASUS PRIME B760M-K', 12500);
        $gpu = $this->makeProduct($this->gpus, 'Видеокарта ASUS GeForce RTX 4060 8GB', 35000);

        $response = $this->postJson('/pc/build?site_access=granted', [
            'customer_name' => 'Иван Иванов',
            'customer_phone' => '+79001234567',
            'customer_city' => 'Смоленск',
            'items' => [
                ['product_id' => $mb->id, 'quantity' => 1],
                ['product_id' => $gpu->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        $order = Order::findOrFail($response->json('order_id'));

        $this->assertSame('Иван Иванов', $order->customer_name);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertCount(2, $order->items);
        $this->assertEquals(82500, (float) $order->total);
        $this->assertStringContainsString('Материнская плата ASUS PRIME B760M-K', $order->customer_comment);

        $gpuItem = $order->items->firstWhere('product_id', $gpu->id);
        $this->assertEquals(35000, (float) $gpuItem->price);
        $this->assertEquals(70000, (float) $gpuItem->total);
    }

    public function test_build_store_validates_payload(): void
    {
        $this->postJson('/pc/build?site_access=granted', [])->assertUnprocessable();

        $this->postJson('/pc/build?site_access=granted', [
            'customer_name' => 'Иван',
            'customer_phone' => '+79001234567',
            'items' => [['product_id' => 9999, 'quantity' => 1]],
        ])->assertUnprocessable();
    }

    public function test_demo_mode_returns_demo_parts(): void
    {
        Setting::set('pc_demo_mode', '1');

        $gpu = $this->makeDemoPart('gpu', 'Видеокарта GeForce RTX 4060 8GB', 38900, [
            'gpu_chip' => 'RTX 4060',
            'vram_gb' => '8',
            'psu_w' => '550',
        ]);

        $slotsResponse = $this->getJson('/api/pc/slots');

        $slotsResponse->assertOk()->assertJsonPath('demo', true);

        $gpuSlot = collect($slotsResponse->json('data'))->firstWhere('id', 'gpu');
        $this->assertFalse($gpuSlot['empty']);

        $response = $this->getJson('/api/pc/parts?slot=gpu');

        $response->assertOk()->assertJsonPath('empty', false);

        $data = collect($response->json('data'));

        $this->assertContains($gpu->id, $data->pluck('id'));
        $this->assertSame('Видеокарта GeForce RTX 4060 8GB', $data->firstWhere('id', $gpu->id)['name']);
    }

    public function test_demo_mode_filters_ram_by_motherboard_memory_type(): void
    {
        Setting::set('pc_demo_mode', '1');

        $mb = $this->makeDemoPart('motherboard', 'Материнская плата ASUS PRIME B760M-K', 12500, [
            'socket' => 'LGA1700',
            'memory_type' => 'DDR5',
            'form_factor' => 'mATX',
        ]);

        $ramDdr5 = $this->makeDemoPart('ram', 'Kingston Fury 16GB DDR5', 5900, ['memory_type' => 'DDR5']);
        $ramDdr4 = $this->makeDemoPart('ram', 'Kingston Fury 16GB DDR4', 3900, ['memory_type' => 'DDR4']);

        $response = $this->getJson('/api/pc/parts?' . http_build_query([
            'slot' => 'ram',
            'build' => json_encode(['motherboard' => $mb->id]),
        ]));

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertContains($ramDdr5->id, $ids);
        $this->assertNotContains($ramDdr4->id, $ids);
    }

    public function test_demo_mode_filters_motherboard_by_cpu_socket(): void
    {
        Setting::set('pc_demo_mode', '1');

        $cpu = $this->makeDemoPart('cpu', 'Процессор AMD Ryzen 7 7800X3D', 39900, [
            'socket' => 'AM5',
            'tdp_w' => '120',
        ]);

        $mbAm5 = $this->makeDemoPart('motherboard', 'Gigabyte B650 AORUS', 18900, ['socket' => 'AM5']);
        $mbLga = $this->makeDemoPart('motherboard', 'ASUS PRIME B760M-K', 12500, ['socket' => 'LGA1700']);

        $response = $this->getJson('/api/pc/parts?' . http_build_query([
            'slot' => 'motherboard',
            'build' => json_encode(['cpu' => $cpu->id]),
        ]));

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertContains($mbAm5->id, $ids);
        $this->assertNotContains($mbLga->id, $ids);
    }

    public function test_demo_mode_off_ignores_demo_parts(): void
    {
        $this->makeDemoPart('gpu', 'Видеокарта GeForce RTX 4060 8GB', 38900, ['gpu_chip' => 'RTX 4060']);

        $response = $this->getJson('/api/pc/parts?slot=gpu');

        $response->assertOk()
            ->assertJsonPath('empty', true)
            ->assertJsonPath('data', []);

        $this->getJson('/api/pc/slots')->assertOk()->assertJsonPath('demo', false);
    }

    public function test_demo_mode_filters_cooler_by_case_clearance(): void
    {
        Setting::set('pc_demo_mode', '1');

        $case = $this->makeDemoPart('case', 'Корпус AeroCool Mini Tower', 4900, [
            'form_factor' => 'mATX,Mini-ITX',
            'cooler_clearance_mm' => '145',
        ]);

        $coolerLow = $this->makeDemoPart('cooler', 'Кулер ID-Cooling SE-214', 2900, ['height_mm' => '140']);
        $coolerHigh = $this->makeDemoPart('cooler', 'Кулер Noctua NH-U12S', 7900, ['height_mm' => '158']);

        $response = $this->getJson('/api/pc/parts?' . http_build_query([
            'slot' => 'cooler',
            'build' => json_encode(['case' => $case->id]),
        ]));

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertContains($coolerLow->id, $ids);
        $this->assertNotContains($coolerHigh->id, $ids);
    }

    public function test_demo_mode_cooler_not_limited_by_case_without_clearance(): void
    {
        Setting::set('pc_demo_mode', '1');

        $case = $this->makeDemoPart('case', 'Корпус Zalman Z3 Mid Tower', 5900, [
            'form_factor' => 'ATX,mATX',
        ]);

        $cooler = $this->makeDemoPart('cooler', 'Кулер Noctua NH-U12S', 7900, ['height_mm' => '158']);

        $response = $this->getJson('/api/pc/parts?' . http_build_query([
            'slot' => 'cooler',
            'build' => json_encode(['case' => $case->id]),
        ]));

        $response->assertOk();

        $this->assertContains($cooler->id, collect($response->json('data'))->pluck('id'));
    }

    private function makeDemoPart(string $slot, string $name, float $price, array $attributes = []): PcDemoPart
    {
        return PcDemoPart::create([
            'slot' => $slot,
            'name' => $name,
            'price' => $price,
            'stock' => 5,
            'attributes' => $attributes,
        ]);
    }

    private function makeProduct(Category $category, string $name, float $price = 1000, float $stock = 5): Product
    {
        $product = Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => 'p-' . uniqid(),
            'is_active' => true,
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'name' => $name,
            'is_active' => true,
        ]);

        Price::create(['offer_id' => $offer->id, 'price' => $price, 'currency' => 'RUB']);
        Stock::create(['offer_id' => $offer->id, 'quantity' => $stock]);

        return $product;
    }

    private function setAttr(Product $product, string $slug, string $value): void
    {
        $attribute = Attribute::firstOrCreate(
            ['slug' => $slug],
            ['name' => $slug, 'type' => 'text', 'is_active' => true]
        );

        ProductAttributeValue::create([
            'product_id' => $product->id,
            'attribute_id' => $attribute->id,
            'value' => $value,
        ]);
    }

    private function attrValue(Product $product, string $slug): ?string
    {
        return ProductAttributeValue::query()
            ->where('product_id', $product->id)
            ->whereHas('attribute', fn ($q) => $q->where('slug', $slug))
            ->value('value');
    }

    public function test_auto_build_returns_config_within_budget(): void
    {
        Setting::set('pc_demo_mode', '1');

        $case = $this->makeDemoPart('case', 'Корпус Zalman Z3 Mid Tower', 5900, [
            'form_factor' => 'ATX,mATX',
            'cooler_clearance_mm' => '160',
        ]);
        $cpu = $this->makeDemoPart('cpu', 'Процессор Intel Core i5-13400F', 19900, [
            'socket' => 'LGA1700',
            'tdp_w' => '65',
        ]);
        $cooler = $this->makeDemoPart('cooler', 'Кулер ID-Cooling SE-214', 2900, ['height_mm' => '150']);
        $motherboard = $this->makeDemoPart('motherboard', 'Материнская плата ASUS PRIME B760M-K', 12500, [
            'socket' => 'LGA1700',
            'memory_type' => 'DDR5',
            'form_factor' => 'mATX',
        ]);
        $gpu = $this->makeDemoPart('gpu', 'Видеокарта GeForce RTX 4060 8GB', 38900, [
            'gpu_chip' => 'RTX 4060',
            'vram_gb' => '8',
            'psu_w' => '550',
        ]);
        $ram = $this->makeDemoPart('ram', 'Оперативная память Kingston Fury 16GB DDR5', 5900, [
            'memory_type' => 'DDR5',
            'module_gb' => '16',
        ]);
        $storage = $this->makeDemoPart('storage', 'SSD Kingston NV2 1TB NVMe', 6900, ['capacity_gb' => '1000']);
        $psu = $this->makeDemoPart('psu', 'Блок питания Corsair RM750 750W', 9900, ['wattage' => '750']);

        $response = $this->postJson('/api/pc/auto-build', [
            'budget' => 200000,
            'purpose' => 'games',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $items = $response->json('data.items');
        $total = $response->json('data.total');

        $this->assertNotEmpty($items);
        $this->assertArrayHasKey('cpu', $items);
        $this->assertArrayHasKey('motherboard', $items);
        $this->assertArrayHasKey('gpu', $items);
        $this->assertArrayHasKey('ram', $items);
        $this->assertArrayHasKey('storage', $items);
        $this->assertArrayHasKey('case', $items);
        $this->assertArrayHasKey('cooler', $items);
        $this->assertArrayHasKey('psu', $items);
        $this->assertLessThanOrEqual(200000, $total);

        $this->assertSame($cpu->id, $items['cpu']['id']);
        $this->assertSame($gpu->id, $items['gpu']['id']);
    }

    public function test_auto_build_fails_when_budget_too_low(): void
    {
        Setting::set('pc_demo_mode', '1');

        $this->makeDemoPart('cpu', 'Процессор Intel Core i5-13400F', 19900, ['socket' => 'LGA1700', 'tdp_w' => '65']);
        $this->makeDemoPart('motherboard', 'Материнская плата ASUS PRIME B760M-K', 12500, [
            'socket' => 'LGA1700',
            'memory_type' => 'DDR5',
            'form_factor' => 'mATX',
        ]);
        $this->makeDemoPart('gpu', 'Видеокарта GeForce RTX 4060 8GB', 38900, ['gpu_chip' => 'RTX 4060', 'vram_gb' => '8', 'psu_w' => '550']);
        $this->makeDemoPart('ram', 'Оперативная память Kingston Fury 16GB DDR5', 5900, ['memory_type' => 'DDR5', 'module_gb' => '16']);
        $this->makeDemoPart('storage', 'SSD Kingston NV2 1TB NVMe', 6900, ['capacity_gb' => '1000']);
        $this->makeDemoPart('case', 'Корпус Zalman Z3 Mid Tower', 5900, ['form_factor' => 'ATX,mATX', 'cooler_clearance_mm' => '160']);
        $this->makeDemoPart('cooler', 'Кулер ID-Cooling SE-214', 2900, ['height_mm' => '150']);
        $this->makeDemoPart('psu', 'Блок питания Corsair RM750 750W', 9900, ['wattage' => '750']);

        $response = $this->postJson('/api/pc/auto-build', [
            'budget' => 10000,
            'purpose' => 'games',
        ]);

        $response->assertOk()->assertJsonPath('success', false);
        $this->assertNotEmpty($response->json('reason'));
    }

    public function test_auto_build_fails_when_required_slot_is_empty(): void
    {
        Setting::set('pc_demo_mode', '1');

        // Нет процессоров — сборка невозможна.
        $this->makeDemoPart('motherboard', 'Материнская плата ASUS PRIME B760M-K', 12500, [
            'socket' => 'LGA1700',
            'memory_type' => 'DDR5',
            'form_factor' => 'mATX',
        ]);

        $response = $this->postJson('/api/pc/auto-build', [
            'budget' => 200000,
        ]);

        $response->assertOk()->assertJsonPath('success', false);
    }

    public function test_manager_request_creates_order_with_comment(): void
    {
        $response = $this->postJson('/pc/manager-request?site_access=granted', [
            'customer_name' => 'Иван Иванов',
            'customer_phone' => '+79001234567',
            'customer_city' => 'Смоленск',
            'budget' => 100000,
            'purpose' => 'Игры',
            'wishes' => 'Тихий корпус, SSD 1 ТБ',
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        $order = Order::findOrFail($response->json('order_id'));

        $this->assertSame('Иван Иванов', $order->customer_name);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertNull($order->total);
        $this->assertCount(0, $order->items);
        $this->assertStringContainsString('Заявка на подбор ПК менеджером:', $order->customer_comment);
        $this->assertStringContainsString('100 000 ₽', $order->customer_comment);
        $this->assertStringContainsString('Игры', $order->customer_comment);
        $this->assertStringContainsString('Тихий корпус, SSD 1 ТБ', $order->customer_comment);
    }

    public function test_parts_accepts_multiselect_build_ids(): void
    {
        Setting::set('pc_demo_mode', '1');

        $mb = $this->makeDemoPart('motherboard', 'Материнская плата ASUS PRIME B760M-K', 12500, [
            'socket' => 'LGA1700',
            'memory_type' => 'DDR5',
            'form_factor' => 'mATX',
        ]);

        $ram1 = $this->makeDemoPart('ram', 'Оперативная память Kingston Fury 16GB DDR5', 5900, ['memory_type' => 'DDR5']);
        $ram2 = $this->makeDemoPart('ram', 'Оперативная память Corsair 32GB DDR5', 11900, ['memory_type' => 'DDR5']);

        $response = $this->getJson('/api/pc/parts?' . http_build_query([
            'slot' => 'ram',
            'build' => json_encode(['motherboard' => $mb->id, 'ram' => [$ram1->id, $ram2->id]]),
        ]));

        $response->assertOk()->assertJsonPath('empty', false);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($ram1->id, $ids);
        $this->assertContains($ram2->id, $ids);
    }

    public function test_build_store_accepts_multiple_extra_items(): void
    {
        $mb = $this->makeProduct($this->motherboards, 'Материнская плата ASUS PRIME B760M-K', 12500);
        $extra1 = $this->makeProduct($this->gpus, 'Доп. вентиляторы Arctic P12', 2400);
        $extra2 = $this->makeProduct($this->gpus, 'Wi-Fi адаптер TP-Link', 1900);

        $response = $this->postJson('/pc/build?site_access=granted', [
            'customer_name' => 'Иван Иванов',
            'customer_phone' => '+79001234567',
            'items' => [
                ['product_id' => $mb->id, 'quantity' => 1],
                ['product_id' => $extra1->id, 'quantity' => 1],
                ['product_id' => $extra2->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        $order = Order::findOrFail($response->json('order_id'));
        $this->assertCount(3, $order->items);
        $this->assertEquals(16800, (float) $order->total);
    }

    public function test_build_store_accepts_quantity_for_multiselect_slots(): void
    {
        $mb = $this->makeProduct($this->motherboards, 'Материнская плата ASUS PRIME B760M-K', 12500);
        $ram = $this->makeProduct($this->ram, 'Оперативная память Kingston Fury 16GB DDR5', 5900);
        $fan = $this->makeProduct($this->gpus, 'Доп. вентиляторы Arctic P12', 2400);

        $response = $this->postJson('/pc/build?site_access=granted', [
            'customer_name' => 'Иван Иванов',
            'customer_phone' => '+79001234567',
            'items' => [
                ['product_id' => $mb->id, 'quantity' => 1],
                ['product_id' => $ram->id, 'quantity' => 2],
                ['product_id' => $fan->id, 'quantity' => 3],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        $order = Order::findOrFail($response->json('order_id'));
        $this->assertCount(3, $order->items);
        $this->assertEquals(31500, (float) $order->total);

        $ramItem = $order->items->firstWhere('product_id', $ram->id);
        $this->assertEquals(2, $ramItem->quantity);
        $this->assertEquals(11800, (float) $ramItem->total);

        $fanItem = $order->items->firstWhere('product_id', $fan->id);
        $this->assertEquals(3, $fanItem->quantity);
        $this->assertEquals(7200, (float) $fanItem->total);
    }
}
