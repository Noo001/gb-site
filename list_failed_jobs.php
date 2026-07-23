<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$jobs = DB::table('failed_jobs')->orderBy('id', 'desc')->limit(5)->get();
foreach ($jobs as $j) {
    echo 'id=' . $j->id . ' | ' . $j->connection . ' | ' . $j->queue . ' | exception=' . substr($j->exception ?? '', 0, 500) . PHP_EOL;
}
