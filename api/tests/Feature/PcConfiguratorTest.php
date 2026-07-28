<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductAttributeValue;
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
            ['case', 'cpu', 'motherboard', 'gpu', 'ram', 'storage', 'psu', 'extra'],
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
}
