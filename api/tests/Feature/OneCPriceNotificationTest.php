<?php

namespace Tests\Feature;

use App\Jobs\NotifyPriceChangedTo1C;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OneCPriceNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.1c.api_key', config('services.1c.api_key') ?: 'test-1c-key');
    }

    public function test_price_change_dispatches_notification_to_1c(): void
    {
        Queue::fake();

        $category = Category::create(['name' => 'Apple', 'slug' => 'apple']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'iPhone 16',
            'slug' => 'iphone-16',
            'uuid_1c' => '550e8400-e29b-41d4-a716-446655440000',
            'is_active' => true,
        ]);
        $offer = Offer::create([
            'product_id' => $product->id,
            'name' => 'iPhone 16 128GB',
            'is_active' => true,
        ]);

        Price::create([
            'offer_id' => $offer->id,
            'price' => 89990,
            'currency' => 'RUB',
        ]);

        Queue::assertPushed(NotifyPriceChangedTo1C::class, function (NotifyPriceChangedTo1C $job) {
            return $job->uuid1c === '550e8400-e29b-41d4-a716-446655440000'
                && $job->newPrice === 89990.0
                && $job->source === 'admin_panel';
        });
    }

    public function test_1c_price_sync_does_not_dispatch_notification(): void
    {
        Queue::fake();

        $category = Category::create(['name' => 'Apple', 'slug' => 'apple']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'iPhone 16',
            'slug' => 'iphone-16',
            'uuid_1c' => '550e8400-e29b-41d4-a716-446655440001',
            'is_active' => true,
        ]);

        $this->withHeader('X-1C-API-Key', 'test-1c-key')
            ->postJson('/api/1c/prices/sync', [
                'items' => [
                    [
                        'uuid_1c' => '550e8400-e29b-41d4-a716-446655440001',
                        'price' => 79990,
                        'currency' => 'RUB',
                    ],
                ],
            ])
            ->assertOk();

        Queue::assertNotPushed(NotifyPriceChangedTo1C::class);
    }
}
