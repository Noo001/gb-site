<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$jobs = DB::table('failed_jobs')->orderBy('id', 'desc')->limit(5)->get();
foreach ($jobs as $j) {
    echo '=== id=' . $j->id . ' ===' . PHP_EOL;
    echo ($j->exception ?? 'no exception') . PHP_EOL;
    echo PHP_EOL;
}
