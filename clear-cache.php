<?php
const BASE_DIR = '/home/m/mastak97/gbsale.ru/api';
chdir(BASE_DIR);
require BASE_DIR . '/vendor/autoload.php';

$app = require_once BASE_DIR . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$commands = [
    'migrate --force',
    'config:clear',
    'route:clear',
    'view:clear',
    'event:clear',
];

foreach ($commands as $cmd) {
    echo ">>> $cmd\n";
    try {
        $status = Artisan::call($cmd);
        echo Artisan::output();
        echo "exit: $status\n\n";
    } catch (Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "=== DONE ===\n";
