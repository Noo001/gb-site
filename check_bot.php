<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'bot_products=' . App\Models\BotProduct::count() . PHP_EOL;
echo 'products=' . App\Models\Product::count() . PHP_EOL;
echo 'categories=' . App\Models\Category::count() . PHP_EOL;
