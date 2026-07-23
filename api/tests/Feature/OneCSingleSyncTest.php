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
