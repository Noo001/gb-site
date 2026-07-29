import subprocess

php_code = r"""<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Categories total: " . App\Models\Category::count() . "\n";
echo "Categories on menu (parent_id is null): " . App\Models\Category::whereNull('parent_id')->count() . "\n";
echo "Active products: " . App\Models\Product::where('is_active', true)->count() . "\n";
echo "Active products with images: " . App\Models\Product::where('is_active', true)->whereHas('media')->count() . "\n";
"""

ssh_cmd = f"cat > /tmp/check_perf.php <<'EOF'\n{php_code}\nEOF\n/usr/local/bin/php8.4 /tmp/check_perf.php"

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
