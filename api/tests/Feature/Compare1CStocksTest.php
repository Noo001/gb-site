<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Offer;
use App\Models\OneCStocksSnapshot;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Compare1CStocksTest extends TestCase
{
    use RefreshDatabase;

    public function test_compare_stocks_finds_mismatches(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        $product = Product::create([
            'uuid_1c' => '11111111-1111-1111-1111-111111111111',
            'category_id' => $category->id,
            'name' => 'Test',
            'slug' => 'test',
            'is_active' => true,
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'external_id' => 'offer-1',
            'name' => 'Test offer',
            'is_active' => true,
        ]);

        $store = Store::create([
            'external_id' => 'store-1',
            'name' => 'Москва',
            'type' => Store::TYPE_STORE,
            'is_active' => true,
        ]);

        // На сайте 5 шт.
        Stock::create(['offer_id' => $offer->id, 'store_id' => $store->id, 'quantity' => 5]);

        // В 1С snapshot 3 шт.
        OneCStocksSnapshot::create([
            'batch_id' => 'batch-1',
            'offer_external_id' => 'offer-1',
            'store_external_id' => 'store-1',
            'quantity' => 3,
        ]);

        // Запись только в 1С.
        OneCStocksSnapshot::create([
            'batch_id' => 'batch-1',
            'offer_external_id' => 'offer-2',
            'store_external_id' => 'store-1',
            'quantity' => 7,
        ]);

        $this->artisan('1c:compare-stocks --batch-id=batch-1')
            ->assertSuccessful()
            ->expectsOutputToContain('Результат сравнения')
            ->expectsOutputToContain('Расходятся по количеству');
    }

    public function test_compare_stocks_no_mismatch(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        $product = Product::create([
            'uuid_1c' => '11111111-1111-1111-1111-111111111111',
            'category_id' => $category->id,
            'name' => 'Test',
            'slug' => 'test',
            'is_active' => true,
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'external_id' => 'offer-1',
            'name' => 'Test offer',
            'is_active' => true,
        ]);

        $store = Store::create([
            'external_id' => 'store-1',
            'name' => 'Москва',
            'type' => Store::TYPE_STORE,
            'is_active' => true,
        ]);

        Stock::create(['offer_id' => $offer->id, 'store_id' => $store->id, 'quantity' => 5]);

        OneCStocksSnapshot::create([
            'batch_id' => 'batch-1',
            'offer_external_id' => 'offer-1',
            'store_external_id' => 'store-1',
            'quantity' => 5,
        ]);

        $this->artisan('1c:compare-stocks --batch-id=batch-1')
            ->assertSuccessful();
    }
}
