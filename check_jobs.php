<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'failed_jobs=' . DB::table('failed_jobs')->count() . PHP_EOL;
echo 'jobs=' . DB::table('jobs')->count() . PHP_EOL;
$job = DB::table('jobs')->first();
if ($job) {
    echo 'first_job_payload=' . substr($job->payload, 0, 200) . PHP_EOL;
}
