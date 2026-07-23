<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'products=' . App\Models\Product::count() . PHP_EOL;
echo 'categories=' . App\Models\Category::count() . PHP_EOL;
echo 'integration_logs=' . App\Models\IntegrationLog::count() . PHP_EOL;
$log = App\Models\IntegrationLog::latest()->first();
if ($log) {
    echo 'last_log=method=' . $log->method . ' code=' . $log->response_code . ' path=' . $log->path . PHP_EOL;
} else {
    echo 'last_log=none' . PHP_EOL;
}
echo '1c_products=' . App\Models\OneCProduct::count() . PHP_EOL;
echo '1c_categories=' . App\Models\OneCCategory::count() . PHP_EOL;
