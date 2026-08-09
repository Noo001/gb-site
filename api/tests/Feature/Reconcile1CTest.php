<?php

namespace Tests\Feature;

use App\Models\BotProduct;
use App\Models\Category;
use App\Models\IntegrationLog;
use App\Models\Offer;
use App\Models\Price;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Reconcile1CTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_counts_missing_and_mismatch(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        $linkedProduct = Product::create([
            'uuid_1c' => '11111111-1111-1111-1111-111111111111',
            'category_id' => $category->id,
            'name' => 'Linked',
            'slug' => 'linked',
            'is_active' => true,
        ]);
        $linkedOffer = Offer::create([
            'product_id' => $linkedProduct->id,
            'external_id' => 'offer-1',
            'name' => 'Linked offer',
            'is_active' => true,
        ]);
        Price::create(['offer_id' => $linkedOffer->id, 'price' => 10000, 'currency' => 'RUB']);

        $store = Store::create(['external_id' => 'store-1', 'name' => 'Москва', 'type' => Store::TYPE_STORE, 'is_active' => true]);
        $serviceStore = Store::create(['external_id' => 'store-service', 'name' => 'РЦ тестовый', 'type' => Store::TYPE_SERVICE, 'is_active' => false]);

        Stock::create(['offer_id' => $linkedOffer->id, 'store_id' => $store->id, 'quantity' => 5]);
        // Остаток на service-складе не должен участвовать в продажном остатке.
        Stock::create(['offer_id' => $linkedOffer->id, 'store_id' => $serviceStore->id, 'quantity' => 100]);

        $junkProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Junk',
            'slug' => 'junk',
            'is_active' => true,
        ]);

        $orphanProduct = Product::create([
            'uuid_1c' => '22222222-2222-2222-2222-222222222222',
            'category_id' => $category->id,
            'name' => 'Orphan',
            'slug' => 'orphan',
            'is_active' => true,
        ]);

        BotProduct::create([
            'offer_id' => $linkedOffer->id,
            'product_id' => $linkedProduct->id,
            'name' => 'Linked',
            'price' => 9000,
            'quantity' => 3,
            'availability' => 'in_stock',
        ]);

        $this->artisan('reconcile:1c --dry-run')
            ->assertSuccessful()
            ->expectsOutputToContain('Отчёт сверки с 1С')
            ->expectsOutputToContain('Без uuid_1c (мусор/демо)')
            ->expectsOutputToContain('Не найдены в 1С')
            ->expectsOutputToContain('Расхождения цен')
            ->expectsOutputToContain('Расхождения остатков');
    }

    public function test_deactivate_missing_flag_deactivates_junk(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        $junkProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Junk',
            'slug' => 'junk',
            'is_active' => true,
        ]);

        $this->artisan('reconcile:1c --deactivate-missing')
            ->assertSuccessful();

        $this->assertFalse($junkProduct->fresh()->is_active);
    }

    public function test_cleanup_logs_flag_deletes_old_logs(): void
    {
        $oldLog = IntegrationLog::create([
            'direction' => 'in',
            'system' => '1c',
            'endpoint' => '/api/1c/products',
            'method' => 'POST',
        ]);
        $oldLog->created_at = Carbon::now()->subDays(31);
        $oldLog->save();

        $recentLog = IntegrationLog::create([
            'direction' => 'in',
            'system' => '1c',
            'endpoint' => '/api/1c/products',
            'method' => 'POST',
        ]);
        $recentLog->created_at = Carbon::now()->subDays(5);
        $recentLog->save();

        $this->artisan('reconcile:1c --cleanup-logs')
            ->assertSuccessful();

        $this->assertDatabaseMissing('integration_logs', ['id' => $oldLog->id]);
        $this->assertDatabaseHas('integration_logs', ['id' => $recentLog->id]);
    }

    public function test_service_store_stock_ignored(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        $linkedProduct = Product::create([
            'uuid_1c' => '11111111-1111-1111-1111-111111111111',
            'category_id' => $category->id,
            'name' => 'Linked',
            'slug' => 'linked',
            'is_active' => true,
        ]);
        $linkedOffer = Offer::create([
            'product_id' => $linkedProduct->id,
            'external_id' => 'offer-1',
            'name' => 'Linked offer',
            'is_active' => true,
        ]);
        Price::create(['offer_id' => $linkedOffer->id, 'price' => 10000, 'currency' => 'RUB']);

        $store = Store::create(['external_id' => 'store-1', 'name' => 'Москва', 'type' => Store::TYPE_STORE, 'is_active' => true]);
        $serviceStore = Store::create(['external_id' => 'store-service', 'name' => 'РЦ тестовый', 'type' => Store::TYPE_SERVICE, 'is_active' => false]);

        Stock::create(['offer_id' => $linkedOffer->id, 'store_id' => $store->id, 'quantity' => 5]);
        Stock::create(['offer_id' => $linkedOffer->id, 'store_id' => $serviceStore->id, 'quantity' => 100]);

        BotProduct::create([
            'offer_id' => $linkedOffer->id,
            'product_id' => $linkedProduct->id,
            'name' => 'Linked',
            'price' => 10000,
            'quantity' => 5, // совпадает с продажным остатком (без service-склада)
            'availability' => 'in_stock',
        ]);

        $this->artisan('reconcile:1c --dry-run')
            ->assertSuccessful()
            ->expectsOutputToContain('Расхождения остатков');

        // Service-склад не должен участвовать в продажном остатке.
        $this->assertEquals(5.0, $linkedProduct->totalStock());
    }
}
