<?php

namespace Tests\Feature;

use App\Jobs\Apply1CStagingData;
use App\Jobs\RebuildBotIndexJob;
use App\Models\Category;
use App\Models\OneCCategory;
use App\Models\OneCOffer;
use App\Models\OneCPrice;
use App\Models\OneCProduct;
use App\Models\OneCStock;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OneCBulkSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.1c.api_key', config('services.1c.api_key') ?: 'test-1c-key');
    }

    public function test_bulk_sync_creates_staging_records_and_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/bulk-sync', [
                'categories' => [
                    [
                        'external_id' => 'cat-1',
                        'name' => 'Смартфоны',
                        'is_active' => true,
                    ],
                ],
                'products' => [
                    [
                        'external_id' => '550e8400-e29b-41d4-a716-446655440000',
                        'category_external_id' => 'cat-1',
                        'name' => 'iPhone 17 Pro Max',
                        'sku' => 'IP17PM256NT',
                        'brand' => 'Apple',
                        'price' => 149990,
                        'currency' => 'RUB',
                        'quantity' => 5,
                    ],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Данные приняты в обработку. Индекс бота будет обновлён после применения.')
            ->assertJsonStructure(['batch_id', 'statistics']);

        $batchId = $response->json('batch_id');

        $this->assertDatabaseHas('1c_categories', [
            'batch_id' => $batchId,
            'external_id' => 'cat-1',
            'name' => 'Смартфоны',
        ]);

        $this->assertDatabaseHas('1c_products', [
            'batch_id' => $batchId,
            'external_id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'iPhone 17 Pro Max',
        ]);

        Queue::assertPushed(Apply1CStagingData::class, function (Apply1CStagingData $job) use ($batchId) {
            return $job->batchId === $batchId;
        });

        Queue::assertNotPushed(RebuildBotIndexJob::class);
    }

    public function test_bulk_sync_dispatches_bot_index_rebuild_after_successful_apply(): void
    {
        $batchId = 'batch-bot-test';

        Category::create([
            'external_id' => 'cat-1',
            'name' => 'Смартфоны',
            'slug' => 'smartfony',
        ]);

        \App\Models\OneCProduct::create([
            'batch_id' => $batchId,
            'external_id' => '550e8400-e29b-41d4-a716-446655440000',
            'category_external_id' => 'cat-1',
            'name' => 'iPhone',
            'raw' => [
                'is_active' => true,
                'images_urls' => [],
                'attributes' => [],
            ],
        ]);

        (new Apply1CStagingData($batchId))->handle();

        $this->assertDatabaseHas('products', [
            'uuid_1c' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $this->assertDatabaseHas('bot_products', [
            'name' => 'iPhone',
        ]);
    }

    public function test_apply_staging_data_creates_catalog_records(): void
    {
        $batchId = 'batch-test-1';

        OneCCategory::create([
            'batch_id' => $batchId,
            'external_id' => 'cat-1',
            'name' => 'Смартфоны',
            'raw' => ['is_active' => true, 'sort' => 0],
        ]);

        OneCProduct::create([
            'batch_id' => $batchId,
            'external_id' => '550e8400-e29b-41d4-a716-446655440000',
            'category_external_id' => 'cat-1',
            'name' => 'iPhone 17 Pro Max',
            'raw' => [
                'sku' => 'IP17PM256NT',
                'brand' => 'Apple',
                'is_active' => true,
                'images_urls' => [],
                'attributes' => [],
            ],
        ]);

        OneCOffer::create([
            'batch_id' => $batchId,
            'external_id' => '550e8400-e29b-41d4-a716-446655440000',
            'product_external_id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'iPhone 17 Pro Max 256GB',
            'sku' => 'IP17PM256NT',
            'raw' => [],
        ]);

        OneCPrice::create([
            'batch_id' => $batchId,
            'offer_external_id' => '550e8400-e29b-41d4-a716-446655440000',
            'price' => 149990,
            'currency' => 'RUB',
            'raw' => [],
        ]);

        OneCStock::create([
            'batch_id' => $batchId,
            'offer_external_id' => '550e8400-e29b-41d4-a716-446655440000',
            'quantity' => 5,
            'raw' => [],
        ]);

        (new Apply1CStagingData($batchId))->handle();

        $this->assertDatabaseHas('categories', [
            'external_id' => 'cat-1',
            'name' => 'Смартфоны',
        ]);

        $this->assertDatabaseHas('products', [
            'uuid_1c' => '550e8400-e29b-41d4-a716-446655440000',
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
    }

    public function test_bulk_sync_status_returns_statistics(): void
    {
        Queue::fake();

        $response = $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/bulk-sync', [
                'products' => [
                    [
                        'external_id' => '550e8400-e29b-41d4-a716-446655440000',
                        'name' => 'iPhone 17',
                    ],
                ],
            ]);

        $batchId = $response->json('batch_id');

        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->getJson("/api/1c/bulk-sync/{$batchId}/status")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('statistics.products', 1);
    }

    public function test_bulk_sync_requires_api_key(): void
    {
        $this->postJson('/api/1c/bulk-sync', [])
            ->assertUnauthorized();
    }

    public function test_bulk_sync_validates_product_external_id(): void
    {
        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/bulk-sync', [
                'products' => [
                    ['name' => 'Без external_id'],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['products.0.external_id']);
    }

    public function test_rebuild_bot_index_endpoint_dispatches_job(): void
    {
        Queue::fake();

        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/bot/rebuild-index')
            ->assertOk()
            ->assertJsonPath('success', true);

        Queue::assertPushed(RebuildBotIndexJob::class);
    }
}
