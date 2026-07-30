<?php
$log = __DIR__ . '/../api/storage/logs/laravel-' . date('Y-m-d') . '.log';
if (!file_exists($log)) {
    echo "Log not found: $log\n";
    exit(1);
}
echo "=== Last 150 lines of $log ===\n";
$lines = file($log);
foreach (array_slice($lines, -150) as $line) {
    echo $line;
}
