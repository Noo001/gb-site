<?php
const BASE_DIR = '/home/m/mastak97/gbsale.ru/api';
chdir(BASE_DIR);
require BASE_DIR . '/vendor/autoload.php';

$app = require_once BASE_DIR . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Artisan::call('migrate:status');
echo Artisan::output();
