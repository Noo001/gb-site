<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$items = App\Models\OneCProduct::orderBy('id', 'desc')->limit(10)->get();
foreach ($items as $i) {
    echo $i->id . ' | ' . $i->name . ' | processed=' . ($i->processed_at ?? 'NULL') . ' | error=' . ($i->error ?? 'NULL') . ' | batch=' . $i->batch_id . PHP_EOL;
}
