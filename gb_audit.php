<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function dup(string $table, string $col): int {
    return DB::table($table)->select($col)->groupBy($col)->havingRaw('COUNT(*) > 1')->count();
}

echo "=== СЧЁТЧИКИ ===\n";
foreach (['categories','products','offers','prices','stocks','stores','bot_products'] as $t) {
    echo "$t=" . DB::table($t)->count() . "  ";
}
echo "\nproducts_active=" . DB::table('products')->where('is_active', true)->count() . "\n";

echo "\n=== ДУБЛИ (external ключи) ===\n";
echo "products.uuid_1c: " . dup('products', 'uuid_1c') . "\n";
echo "offers.external_id: " . dup('offers', 'external_id') . "\n";
echo "categories.external_id: " . dup('categories', 'external_id') . "\n";
echo "stores.external_id: " . dup('stores', 'external_id') . "\n";
echo "prices(offer+region+store): " . DB::table('prices')->select(DB::raw('offer_id, coalesce(region_id,0), coalesce(store_id,0)'))->groupByRaw('offer_id, coalesce(region_id,0), coalesce(store_id,0)')->havingRaw('COUNT(*)>1')->count() . "\n";
echo "stocks(offer+store): " . DB::table('stocks')->select(DB::raw('offer_id, store_id'))->groupBy('offer_id','store_id')->havingRaw('COUNT(*)>1')->count() . "\n";

echo "\n=== СИРОТЫ ===\n";
echo "offers без product: " . DB::table('offers')->leftJoin('products','offers.product_id','products.id')->whereNull('products.id')->count() . "\n";
echo "prices без offer: " . DB::table('prices')->leftJoin('offers','prices.offer_id','offers.id')->whereNull('offers.id')->count() . "\n";
echo "stocks без offer: " . DB::table('stocks')->leftJoin('offers','stocks.offer_id','offers.id')->whereNull('offers.id')->count() . "\n";
echo "products без category: " . DB::table('products')->whereNull('category_id')->count() . "\n";

echo "\n=== ЦЕНЫ/ОСТАТКИ ===\n";
echo "offers с ценой=0: " . DB::table('prices')->where('price', 0)->count() . "\n";
echo "products активных без цены: " . DB::table('products')->where('is_active', true)->whereNotExists(function ($q) { $q->from('offers')->whereColumn('offers.product_id','products.id')->whereExists(function ($q2) { $q2->from('prices')->whereColumn('prices.offer_id','offers.id')->where('prices.price','>',0); }); })->count() . "\n";
echo "stocks quantity<0: " . DB::table('stocks')->where('quantity', '<', 0)->count() . "\n";

echo "\n=== ОБМЕН 1С ===\n";
echo "failed_1c_exports pending: " . DB::table('failed_1c_exports')->whereNull('processed_at')->count() . "\n";
echo "failed_jobs: " . DB::table('failed_jobs')->count() . "\n";
echo "jobs: " . DB::table('jobs')->count() . "\n";
echo "логи за 24ч: " . DB::table('integration_logs')->where('created_at', '>', now()->subDay())->count() . "\n";
echo "последний лог: " . DB::table('integration_logs')->max('created_at') . "\n";
echo "endpoint за 24ч:\n";
foreach (DB::table('integration_logs')->where('created_at', '>', now()->subDay())->select('endpoint', DB::raw('count(*) as c'))->groupBy('endpoint')->get() as $r) {
    echo "  {$r->endpoint}: {$r->c}\n";
}

echo "\n=== СКЛАДЫ ===\n";
echo "живых: " . App\Models\Store::count() . ", мёртвых(soft): " . DB::table('stores')->whereNotNull('deleted_at')->count() . "\n";
echo "подразделения с остатками: " . App\Models\Store::where('name','like','(Подразделение)%')->whereExists(function ($q) { $q->from('stocks')->whereColumn('stocks.store_id','stores.id')->where('quantity','>',0); })->count() . "\n";
echo "дубли имён складов: " . App\Models\Store::select('name')->groupBy('name')->havingRaw('COUNT(*)>1')->count() . "\n";

echo "\n=== БОТ ===\n";
echo "bot_products: " . DB::table('bot_products')->count() . " (products_active=" . DB::table('products')->where('is_active', true)->count() . ")\n";
echo "bot_products.updated_at max: " . DB::table('bot_products')->max('updated_at') . "\n";
echo "bot_products без цены>0: " . DB::table('bot_products')->where('price', '<=', 0)->count() . "\n";
