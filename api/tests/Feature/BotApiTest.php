<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\BotKnowledge;
use App\Models\BotProduct;
use App\Models\BotTradeInPrice;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BotApiTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey = 'test-bot-key';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.bot.api_key', $this->apiKey);
    }

    private function withBotKey(): self
    {
        return $this->withHeader('X-Bot-API-Key', $this->apiKey);
    }

    public function test_search_products_requires_api_key(): void
    {
        $response = $this->postJson('/api/bot/products/search', ['query' => 'iphone']);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_search_products_returns_indexed_data(): void
    {
        $store = Store::create([
            'name' => 'Воронежский магазин',
            'city' => 'Воронеж',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Apple',
            'slug' => 'apple',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Apple iPhone 17 Pro Max',
            'slug' => 'iphone-17-pro-max',
            'brand' => 'Apple',
            'url' => '/product/iphone-17-pro-max',
            'is_active' => true,
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'name' => 'Apple iPhone 17 Pro Max 256GB Natural Titanium',
            'sku' => 'IP17PM256NT',
            'is_active' => true,
        ]);

        Price::create([
            'offer_id' => $offer->id,
            'price' => 149990,
            'currency' => 'RUB',
        ]);

        Stock::create([
            'offer_id' => $offer->id,
            'store_id' => $store->id,
            'quantity' => 3,
            'reserved' => 0,
        ]);

        BotProduct::create([
            'offer_id' => $offer->id,
            'product_id' => $product->id,
            'name' => $offer->name,
            'brand' => 'Apple',
            'category' => 'Apple',
            'price' => 149990,
            'currency' => 'RUB',
            'availability' => 'in_stock',
            'quantity' => 3,
            'url' => $product->url,
            'available_in_cities' => ['Воронеж'],
            'city_availability' => [
                'Воронеж' => ['available' => true, 'quantity' => 3, 'store_id' => $store->id],
            ],
            'metadata' => ['storage' => '256GB', 'color' => 'Natural Titanium'],
            'search_text' => $offer->name . ' Apple ' . $product->name,
            'is_active' => true,
            'updated_at' => now(),
        ]);

        $response = $this->withBotKey()->postJson('/api/bot/products/search', [
            'query' => 'iPhone 17 Pro Max',
        ]);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', $offer->name)
            ->assertJsonPath('0.price', '149990.00')
            ->assertJsonPath('0.availability', 'in_stock')
            ->assertJsonPath('0.available_in_cities', ['Воронеж'])
            ->assertJsonPath('0.metadata.storage', '256GB');
    }

    public function test_search_products_accepts_parameters0_string(): void
    {
        BotProduct::create([
            'offer_id' => 1,
            'product_id' => 1,
            'name' => 'Samsung Galaxy S25',
            'brand' => 'Samsung',
            'category' => 'Samsung',
            'price' => 99990,
            'currency' => 'RUB',
            'availability' => 'out_of_stock',
            'quantity' => 0,
            'url' => '/product/s25',
            'available_in_cities' => [],
            'city_availability' => [],
            'metadata' => [],
            'search_text' => 'Samsung Galaxy S25',
            'is_active' => true,
            'updated_at' => now(),
        ]);

        $parameters0 = json_encode([
            'query' => 'Galaxy S25',
            'filter' => ['brand_contains' => 'samsung'],
            'ranking' => ['availability_boost' => true],
        ]);

        $response = $this->withBotKey()->postJson('/api/bot/products/search', [
            'parameters0' => $parameters0,
        ]);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Samsung Galaxy S25');
    }

    public function test_search_products_filters_by_availability(): void
    {
        BotProduct::create([
            'offer_id' => 1,
            'product_id' => 1,
            'name' => 'In-stock item',
            'brand' => 'Xiaomi',
            'category' => 'Xiaomi',
            'price' => 10000,
            'availability' => 'in_stock',
            'quantity' => 5,
            'url' => '/product/in',
            'available_in_cities' => ['Москва'],
            'city_availability' => [],
            'metadata' => [],
            'search_text' => 'In-stock item',
            'is_active' => true,
            'updated_at' => now(),
        ]);

        BotProduct::create([
            'offer_id' => 2,
            'product_id' => 2,
            'name' => 'Out-of-stock item',
            'brand' => 'Xiaomi',
            'category' => 'Xiaomi',
            'price' => 10000,
            'availability' => 'out_of_stock',
            'quantity' => 0,
            'url' => '/product/out',
            'available_in_cities' => [],
            'city_availability' => [],
            'metadata' => [],
            'search_text' => 'Out-of-stock item',
            'is_active' => true,
            'updated_at' => now(),
        ]);

        $response = $this->withBotKey()->postJson('/api/bot/products/search', [
            'filter' => ['availability' => 'in_stock'],
        ]);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'In-stock item');
    }

    public function test_get_config_by_group_and_key(): void
    {
        BotKnowledge::create([
            'type' => 'config',
            'group' => 'payment',
            'key' => 'installments',
            'payload' => [
                'title' => 'Рассрочка',
                'text' => 'Доступна рассрочка 0%.',
            ],
            'is_active' => true,
        ]);

        $response = $this->withBotKey()->postJson('/api/bot/config', [
            'p_group' => 'payment',
            'p_key' => 'installments',
        ]);

        $response->assertOk()
            ->assertJsonPath('title', 'Рассрочка')
            ->assertJsonPath('text', 'Доступна рассрочка 0%.');
    }

    public function test_get_stores(): void
    {
        Store::create([
            'name' => 'Gadget Bar Воронеж',
            'city' => 'Воронеж',
            'address' => 'ул. Примерная, 10',
            'phone' => '+7 (473) 000-00-00',
            'schedule' => '10:00–22:00',
            'is_active' => true,
        ]);

        Store::create([
            'name' => 'Gadget Bar Москва',
            'city' => 'Москва',
            'address' => 'ул. Главная, 1',
            'is_active' => true,
        ]);

        $response = $this->withBotKey()->postJson('/api/bot/stores', [
            'p_city' => 'Воронеж',
        ]);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Gadget Bar Воронеж')
            ->assertJsonPath('0.city', 'Воронеж');
    }

    public function test_get_tradein_price(): void
    {
        BotTradeInPrice::create([
            'brand' => 'Apple',
            'model' => 'iPhone 15 Pro',
            'storage' => '256',
            'condition' => 'working',
            'price' => 65000,
            'currency' => 'RUB',
            'is_active' => true,
        ]);

        $response = $this->withBotKey()->postJson('/api/bot/tradein', [
            'p_brand' => 'Apple',
            'p_model' => 'iPhone 15 Pro',
            'p_storage' => '256',
            'p_condition' => 'working',
        ]);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.price', '65000.00');
    }

    public function test_find_alternatives(): void
    {
        BotProduct::create([
            'offer_id' => 1,
            'product_id' => 1,
            'name' => 'Requested iPhone 16',
            'brand' => 'Apple',
            'category' => 'Apple',
            'price' => 89990,
            'availability' => 'out_of_stock',
            'quantity' => 0,
            'url' => '/product/iphone-16',
            'available_in_cities' => [],
            'city_availability' => [],
            'metadata' => [],
            'search_text' => 'Requested iPhone 16',
            'is_active' => true,
            'updated_at' => now(),
        ]);

        BotProduct::create([
            'offer_id' => 2,
            'product_id' => 2,
            'name' => 'Alternative iPhone 15',
            'brand' => 'Apple',
            'category' => 'Apple',
            'price' => 79990,
            'availability' => 'in_stock',
            'quantity' => 5,
            'url' => '/product/iphone-15',
            'available_in_cities' => ['Воронеж'],
            'city_availability' => [],
            'metadata' => [],
            'search_text' => 'Alternative iPhone 15',
            'is_active' => true,
            'updated_at' => now(),
        ]);

        $response = $this->withBotKey()->postJson('/api/bot/alternatives', [
            'p_brand' => 'Apple',
            'p_name' => 'Requested iPhone 16',
        ]);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Alternative iPhone 15');
    }

    public function test_search_services(): void
    {
        BotKnowledge::create([
            'type' => 'service',
            'group' => 'repair',
            'key' => 'iPhone 16 Pro',
            'payload' => [
                'title' => 'Замена экрана iPhone 16 Pro',
                'price' => 25000,
                'model' => 'iPhone 16 Pro',
            ],
            'is_active' => true,
        ]);

        $response = $this->withBotKey()->postJson('/api/bot/services', [
            'p_group' => 'repair',
            'p_model' => 'iPhone 16 Pro',
        ]);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Замена экрана iPhone 16 Pro');
    }

    public function test_check_trigger(): void
    {
        BotKnowledge::create([
            'type' => 'trigger',
            'group' => 'escalation',
            'key' => 'оператор',
            'payload' => [
                'action' => 'escalate_to_manager',
                'message' => 'Сейчас переведу на менеджера.',
            ],
            'is_active' => true,
        ]);

        $response = $this->withBotKey()->postJson('/api/bot/triggers/check', [
            'p_message' => 'Позовите оператора, пожалуйста',
        ]);

        $response->assertOk()
            ->assertJsonPath('triggered', true)
            ->assertJsonPath('action', 'escalate_to_manager');
    }

    public function test_log_action(): void
    {
        $response = $this->withBotKey()->postJson('/api/bot/log', [
            'channel' => 'bitrix24',
            'action' => 'message_received',
            'payload' => ['text' => 'hello'],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('bot_action_logs', [
            'channel' => 'bitrix24',
            'action' => 'message_received',
        ]);
    }

    public function test_rebuild_command_populates_bot_products(): void
    {
        $store = Store::create([
            'name' => 'Склад',
            'city' => 'Воронеж',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Apple',
            'slug' => 'apple',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'iPhone 16',
            'slug' => 'iphone-16',
            'brand' => 'Apple',
            'url' => '/product/iphone-16',
            'is_active' => true,
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'name' => 'iPhone 16 128GB Black',
            'is_active' => true,
        ]);

        Price::create([
            'offer_id' => $offer->id,
            'price' => '89990.00',
            'currency' => 'RUB',
        ]);

        Stock::create([
            'offer_id' => $offer->id,
            'store_id' => $store->id,
            'quantity' => 2,
            'reserved' => 0,
        ]);

        $this->artisan('bot:rebuild-index')->assertSuccessful();

        $this->assertDatabaseHas('bot_products', [
            'offer_id' => $offer->id,
            'name' => $offer->name,
            'availability' => 'in_stock',
            'price' => 89990,
        ]);
    }
}
