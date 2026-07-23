<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$items = App\Models\OneCProduct::orderBy('id', 'desc')->limit(10)->get();
foreach ($items as $i) {
    echo $i->id . ' | ' . $i->name . ' | batch=' . $i->batch_id . ' | applied=' . ($i->applied_at ?? 'NULL') . ' | cat=' . $i->category_external_id . PHP_EOL;
}
