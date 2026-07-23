<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OneCSingleSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.1c.api_key', config('services.1c.api_key') ?: 'test-1c-key');
    }

    public function test_sync_product_creates_catalog_record(): void
    {
        $category = Category::create([
            'external_id' => 'cat-1',
            'name' => 'Смартфоны',
            'slug' => 'smartfony',
        ]);

        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/products', [
                'external_id' => '550e8400-e29b-41d4-a716-446655440000',
                'category_external_id' => 'cat-1',
                'name' => 'iPhone 17 Pro Max',
                'sku' => 'IP17PM256NT',
                'brand' => 'Apple',
                'description' => 'Флагман',
                'is_active' => true,
                'price' => 149990,
                'currency' => 'RUB',
                'quantity' => 5,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('products', [
            'uuid_1c' => '550e8400-e29b-41d4-a716-446655440000',
            'category_id' => $category->id,
            'name' => 'iPhone 17 Pro Max',
            'sku' => 'IP17PM256NT',
            'brand' => 'Apple',
        ]);

        $this->assertDatabaseHas('offers', [
            'external_id' => '550e8400-e29b-41d4-a716-446655440000',
            'sku' => 'IP17PM256NT',
        ]);

        $this->assertDatabaseHas('prices', [
            'price' => 149990,
            'currency' => 'RUB',
        ]);

        $this->assertDatabaseHas('stocks', [
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('bot_products', [
            'name' => 'iPhone 17 Pro Max',
            'brand' => 'Apple',
        ]);
    }

    public function test_sync_category_creates_category(): void
    {
        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/categories', [
                'external_id' => 'cat-root',
                'name' => 'Электроника',
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('categories', [
            'external_id' => 'cat-root',
            'name' => 'Электроника',
        ]);
    }

    public function test_sync_price_updates_existing_offer(): void
    {
        $product = Product::create([
            'uuid_1c' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'iPhone',
            'slug' => 'iphone',
            'is_active' => true,
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'external_id' => 'offer-1',
            'name' => 'iPhone 128GB',
            'is_active' => true,
        ]);

        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/prices', [
                'offer_external_id' => 'offer-1',
                'price' => 99990,
                'currency' => 'RUB',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('prices', [
            'offer_id' => $offer->id,
            'price' => 99990,
        ]);
    }

    public function test_sync_stock_updates_existing_offer(): void
    {
        $product = Product::create([
            'uuid_1c' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'iPhone',
            'slug' => 'iphone',
            'is_active' => true,
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'external_id' => 'offer-1',
            'name' => 'iPhone 128GB',
            'is_active' => true,
        ]);

        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/stocks', [
                'offer_external_id' => 'offer-1',
                'store_external_id' => 'main',
                'quantity' => 10,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('stocks', [
            'offer_id' => $offer->id,
            'quantity' => 10,
        ]);
    }

    public function test_stocks_sync_creates_and_renames_store(): void
    {
        $product = Product::create([
            'uuid_1c' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'iPhone',
            'slug' => 'iphone',
            'is_active' => true,
        ]);

        Offer::create([
            'product_id' => $product->id,
            'external_id' => 'offer-1',
            'name' => 'iPhone 128GB',
            'is_active' => true,
        ]);

        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/stocks/sync', [
                'items' => [
                    [
                        'offer_external_id' => 'offer-1',
                        'store_external_id' => 'store-uuid-1',
                        'store_name' => 'ТЦ Гагаринский',
                        'quantity' => 7,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('stores', [
            'external_id' => 'store-uuid-1',
            'name' => 'ТЦ Гагаринский',
        ]);

        // Повторная синхронизация с новым именем переименовывает склад.
        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/stocks/sync', [
                'items' => [
                    [
                        'offer_external_id' => 'offer-1',
                        'store_external_id' => 'store-uuid-1',
                        'store_name' => 'ТЦ Гагаринский, 2 этаж',
                        'quantity' => 7,
                    ],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('stores', [
            'external_id' => 'store-uuid-1',
            'name' => 'ТЦ Гагаринский, 2 этаж',
        ]);
    }

    public function test_stores_sync_manages_city_without_overwriting(): void
    {
        // Создание склада с городом.
        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/stores/sync', [
                'items' => [
                    ['external_id' => 'store-1', 'name' => 'ТЦ Гагаринский', 'city' => 'Москва'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('stores', [
            'external_id' => 'store-1',
            'name' => 'ТЦ Гагаринский',
            'city' => 'Москва',
        ]);

        // Пустой город не затирает заполненный вручную/ранее.
        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/stores/sync', [
                'items' => [
                    ['external_id' => 'store-1', 'name' => 'ТЦ Гагаринский (новое имя)'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('stores', [
            'external_id' => 'store-1',
            'name' => 'ТЦ Гагаринский (новое имя)',
            'city' => 'Москва',
        ]);

        // Новый непустой город обновляет.
        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/stores/sync', [
                'items' => [
                    ['external_id' => 'store-1', 'city' => 'Химки'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('stores', [
            'external_id' => 'store-1',
            'city' => 'Химки',
        ]);
    }

    public function test_product_without_price_is_inactive_until_price_arrives(): void
    {
        // Товар без цены создаётся неактивным.
        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/products', [
                'external_id' => 'prod-no-price',
                'name' => 'Товар без цены',
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertDatabaseHas('products', [
            'uuid_1c' => 'prod-no-price',
            'is_active' => false,
        ]);

        $this->assertDatabaseMissing('bot_products', [
            'name' => 'Товар без цены',
        ]);

        // Приход цены > 0 активирует товар и поднимает его в индекс бота.
        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/prices/sync', [
                'items' => [
                    ['offer_external_id' => 'prod-no-price', 'price' => 1990, 'currency' => 'RUB'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('products', [
            'uuid_1c' => 'prod-no-price',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('bot_products', [
            'name' => 'Товар без цены',
            'price' => 1990,
        ]);
    }

    public function test_zero_price_is_ignored(): void
    {
        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/prices/sync', [
                'items' => [
                    ['offer_external_id' => 'offer-zero', 'name' => 'Нулевой товар', 'price' => 0, 'currency' => 'RUB'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseMissing('prices', ['price' => 0]);
        $this->assertDatabaseMissing('bot_products', ['name' => 'Нулевой товар']);
    }

    public function test_delete_product_deactivates_it(): void
    {
        $product = Product::create([
            'uuid_1c' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'iPhone',
            'slug' => 'iphone',
            'is_active' => true,
        ]);

        Offer::create([
            'product_id' => $product->id,
            'external_id' => 'offer-1',
            'name' => 'iPhone 128GB',
            'is_active' => true,
        ]);

        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/products/delete', [
                'external_id' => '550e8400-e29b-41d4-a716-446655440000',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('action', 'deactivated');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }

    public function test_single_endpoints_require_api_key(): void
    {
        $this->postJson('/api/1c/products', [])->assertUnauthorized();
        $this->postJson('/api/1c/categories', [])->assertUnauthorized();
        $this->postJson('/api/1c/prices', [])->assertUnauthorized();
        $this->postJson('/api/1c/stocks', [])->assertUnauthorized();
    }
}
