<?php
$log = __DIR__ . '/../api/storage/logs/laravel.log';
if (!file_exists($log)) {
    echo "Log not found: $log\n";
    exit(1);
}
echo "=== Last 100 lines of $log ===\n";
$lines = file($log);
foreach (array_slice($lines, -100) as $line) {
    echo $line;
}
