<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    DB::statement('TRUNCATE TABLE categories, products, offers, prices, stocks, attributes, product_attribute_values, bot_products RESTART IDENTITY CASCADE');
    DB::table('1c_categories')->delete();
    DB::table('1c_products')->delete();
    DB::table('1c_offers')->delete();
    DB::table('1c_prices')->delete();
    DB::table('1c_stocks')->delete();
    DB::table('jobs')->delete();
    DB::table('failed_jobs')->delete();
    DB::table('integration_logs')->delete();
    echo "All catalog and staging data cleared.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
