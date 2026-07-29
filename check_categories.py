import subprocess

php_code = r"""<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cats = App\Models\Category::query()
    ->whereIn('name', ['На удаление', 'Для заказа', 'Apple MagSafe Charger', 'Б/У', 'Витринные образцы', 'Обменные устройства', 'Переклейка'])
    ->withCount('products')
    ->get();

foreach ($cats as $cat) {
    echo $cat->name . " (id=" . $cat->id . "): products=" . $cat->products_count . " active=" . $cat->products()->where('is_active', true)->count() . "\n";
}

$zeroNames = App\Models\Product::where('name', '0')->orWhere('name', '')->count();
echo "Products with name '0' or empty: " . $zeroNames . "\n";

$activeNoPrice = App\Models\Product::where('is_active', true)
    ->whereDoesntHave('offers.prices', fn($q) => $q->where('price', '>', 0))
    ->count();
echo "Active products without positive price: " . $activeNoPrice . "\n";
"""

ssh_cmd = f"cat > /tmp/check_categories.php <<'EOF'\n{php_code}\nEOF\n/usr/local/bin/php8.4 /tmp/check_categories.php"

result = subprocess.run([
    "sshpass", "-e", "ssh",
    "-o", "StrictHostKeyChecking=no",
    "-o", "UserKnownHostsFile=/dev/null",
    "mastak97_gbsale@gbsale.ru",
    ssh_cmd
], capture_output=True, text=True)

print(result.stdout)
if result.returncode != 0:
    print("ERR:", result.stderr)
