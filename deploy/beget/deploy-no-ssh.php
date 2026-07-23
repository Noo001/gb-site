<?php
/**
 * One-time deploy helper for Beget shared hosting when SSH is not available.
 *
 * Usage:
 *   1. Upload this file to `api/public/deploy-no-ssh.php` via FTP.
 *   2. Open https://gbsale.ru/deploy-no-ssh.php?token=YOUR_TOKEN in browser.
 *   3. Delete the file from `public/` after successful deploy.
 *
 * Change DEPLOY_TOKEN before uploading!
 */

const DEPLOY_TOKEN = 'change-me-strong-token';
const BASE_DIR     = '/home/m/mastak97/gbsale.ru/api';

if (!isset($_GET['token']) || $_GET['token'] !== DEPLOY_TOKEN) {
    http_response_code(403);
    exit('Forbidden: invalid token.');
}

set_time_limit(0);
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

putenv('HOME=' . dirname(BASE_DIR));
chdir(BASE_DIR);

function run(string $command, int &$exit = 0): string {
    echo ">>> $command\n";
    $output = [];
    exec($command . ' 2>&1', $output, $exit);
    $text = implode("\n", $output);
    echo $text . "\n";
    echo "exit: $exit\n\n";
    return $text;
}

// 1. Ensure .env exists
$envPath = BASE_DIR . '/.env';
if (!file_exists($envPath)) {
    $example = BASE_DIR . '/deploy/beget/.env.beget.example';
    if (!file_exists($example)) {
        exit("ERROR: .env not found and example missing at $example");
    }
    copy($example, $envPath);
    echo "Created .env from example.\n";
} else {
    echo ".env already exists.\n";
}

// 2. Ensure APP_KEY is set
$env = file_get_contents($envPath);
if (!preg_match('/^APP_KEY=base64:/m', $env)) {
    $key = 'base64:' . base64_encode(random_bytes(32));
    $env = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $env);
    if (!preg_match('/^APP_KEY=/m', $env)) {
        $env = "APP_KEY={$key}\n" . $env;
    }
    file_put_contents($envPath, $env);
    echo "Generated APP_KEY.\n";
} else {
    echo "APP_KEY already set.\n";
}

// 3. Install Composer if missing
$composerPhar = BASE_DIR . '/composer.phar';
if (!file_exists($composerPhar)) {
    echo "Downloading composer.phar...\n";
    $installer = BASE_DIR . '/composer-setup.php';
    copy('https://getcomposer.org/installer', $installer);
    run('php ' . escapeshellarg($installer) . ' --install-dir=' . escapeshellarg(BASE_DIR) . ' --filename=composer.phar');
    unlink($installer);
}

// 4. composer install
echo "Running composer install...\n";
run('php ' . escapeshellarg($composerPhar) . ' install --no-dev --optimize-autoloader --no-interaction');

// 5. Clear caches so .env changes take effect
echo "Clearing caches...\n";
run('php artisan config:clear');
run('php artisan route:clear');
run('php artisan view:clear');

// 6. Migrations
echo "Running migrations...\n";
run('php artisan migrate --force');

// 7. Storage link
echo "Creating storage link...\n";
run('php artisan storage:link');

// 8. Cache
echo "Caching config/routes/views/events...\n";
run('php artisan config:cache');
run('php artisan route:cache');
run('php artisan view:cache');
run('php artisan event:cache');

// 9. Bot index
echo "Rebuilding bot index...\n";
run('php artisan bot:rebuild-index');

// 10. Permissions
echo "Fixing permissions...\n";
run('chmod -R 755 ' . escapeshellarg(BASE_DIR . '/storage') . ' ' . escapeshellarg(BASE_DIR . '/bootstrap/cache'));
run('find ' . escapeshellarg(BASE_DIR . '/storage') . ' -type d -exec chmod 775 {} +');
run('find ' . escapeshellarg(BASE_DIR . '/storage') . ' -type f -exec chmod 664 {} +');

echo "=== DEPLOY FINISHED ===\n";
echo "IMPORTANT: delete api/public/deploy-no-ssh.php from the server.\n";
echo "Next steps:\n";
echo "  - Add cron in Beget panel: * * * * * /usr/bin/php " . BASE_DIR . "/artisan schedule:run >> " . BASE_DIR . "/storage/logs/scheduler.log 2>&1\n";
echo "  - Replace test API keys (IMPORT_1C_API_KEY, BOT_API_KEY) with strong keys.\n";
