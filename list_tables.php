<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema='public' ORDER BY table_name");
foreach ($tables as $t) {
    echo $t->table_name . PHP_EOL;
}
