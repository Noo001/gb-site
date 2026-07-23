<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$products = App\Models\Product::orderBy('id', 'desc')->limit(10)->get(['id', 'name', 'sku', 'uuid_1c', 'is_active']);
foreach ($products as $p) {
    echo $p->id . ' | ' . $p->name . ' | ' . $p->sku . ' | ' . $p->uuid_1c . ' | ' . ($p->is_active ? 'active' : 'inactive') . PHP_EOL;
}
