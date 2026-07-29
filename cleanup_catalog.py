import subprocess

php_code = r"""<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$categoryNames = ['На удаление', 'Для заказа', 'Переклейка'];
$categories = App\Models\Category::whereIn('name', $categoryNames)->pluck('id');

$deactivatedProducts = App\Models\Product::whereIn('category_id', $categories)
    ->where('is_active', true)
    ->update(['is_active' => false]);

$deactivatedZeroName = App\Models\Product::where('name', '0')
    ->where('is_active', true)
    ->update(['is_active' => false]);

echo "Deactivated products in junk categories: " . $deactivatedProducts . "\n";
echo "Deactivated products with name '0': " . $deactivatedZeroName . "\n";
"""

ssh_cmd = f"cat > /tmp/cleanup_catalog.php <<'EOF'\n{php_code}\nEOF\n/usr/local/bin/php8.4 /tmp/cleanup_catalog.php"

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
