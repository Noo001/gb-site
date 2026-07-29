import subprocess

php_code = r"""<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = App\Models\Product::where('is_active', true)
    ->whereHas('offers.prices', fn($q) => $q->where('price', '>', 0))
    ->select(['id', 'slug', 'name', 'uuid_1c'])
    ->first();

echo $product ? json_encode($product->toArray()) : 'no product';
echo "\n";
"""

ssh_cmd = f"cat > /tmp/get_product.php <<'EOF'\n{php_code}\nEOF\n/usr/local/bin/php8.4 /tmp/get_product.php"

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
