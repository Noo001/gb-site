<?php
/**
 * Run CommerceML import on Beget shared hosting without SSH.
 *
 * Usage:
 *   1. Upload this file to `api/public/run-import-no-ssh.php` via FTP.
 *   2. Open https://gbsale.ru/run-import-no-ssh.php?token=YOUR_TOKEN in browser.
 *   3. Delete the file from `public/` after successful import.
 *
 * Change RUN_IMPORT_TOKEN before uploading!
 */

const RUN_IMPORT_TOKEN = 'change-me-strong-token';
const BASE_DIR         = '/home/m/mastak97/gbsale.ru/api';
const EXPORT_DIR       = '/home/m/mastak97/gbsale.ru/docs/ВыгрузкаДляБота';
const PHP_BIN          = '/usr/bin/php';
const CHUNK_SIZE       = 500;

if (!isset($_GET['token']) || $_GET['token'] !== RUN_IMPORT_TOKEN) {
    http_response_code(403);
    exit('Forbidden: invalid token.');
}

set_time_limit(0);
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

if (!is_dir(BASE_DIR)) {
    exit('ERROR: BASE_DIR not found: ' . BASE_DIR);
}

if (!is_dir(EXPORT_DIR)) {
    exit('ERROR: Export directory not found: ' . EXPORT_DIR . "\nUpload docs/ВыгрузкаДляБота/ to the server first.");
}

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

// 1. Run CommerceML import with --apply
echo "=== Running CommerceML import ===\n";
$importCmd = escapeshellarg(PHP_BIN) . ' artisan 1c:import-commerceml '
    . escapeshellarg(EXPORT_DIR)
    . ' --apply --chunk=' . (int) CHUNK_SIZE;
run($importCmd, $importExit);

if ($importExit !== 0) {
    echo "=== IMPORT FAILED ===\n";
    exit;
}

// 2. Process queue immediately (since cron may not be set up)
echo "=== Processing queue ===\n";
run(escapeshellarg(PHP_BIN) . ' artisan queue:work --stop-when-empty --timeout=300', $queueExit);

// 3. Rebuild bot index
echo "=== Rebuilding bot index ===\n";
run(escapeshellarg(PHP_BIN) . ' artisan bot:rebuild-index', $indexExit);

echo "=== IMPORT FINISHED ===\n";
echo "IMPORTANT: delete api/public/run-import-no-ssh.php from the server.\n";
