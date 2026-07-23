<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "one_c_staging: " . DB::table('one_c_staging')->count() . "\n";
echo "one_c_prices: " . DB::table('one_c_prices')->count() . "\n";
echo "jobs: " . DB::table('jobs')->count() . "\n";
echo "failed_jobs: " . DB::table('failed_jobs')->count() . "\n";

echo "recent one_c_staging (last 5):\n";
foreach (DB::table('one_c_staging')->orderBy('created_at', 'desc')->limit(5)->get() as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "recent prices staging (last 5):\n";
foreach (DB::table('one_c_prices')->orderBy('created_at', 'desc')->limit(5)->get() as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "recent failed_jobs (last 5):\n";
foreach (DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(5)->get() as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "product price for uuid_1c=5b416a54-d696-11f0-8210-00e04c680230:\n";
$product = DB::table('products')->where('uuid_1c', '5b416a54-d696-11f0-8210-00e04c680230')->first();
if ($product) {
    echo json_encode($product, JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "not found\n";
}

@unlink(__FILE__);
