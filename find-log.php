<?php
$base = __DIR__ . '/../api';
echo "Base: $base\n";
echo "realpath: " . realpath($base) . "\n";

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
foreach ($iter as $file) {
    if ($file->isFile() && strpos($file->getFilename(), 'laravel') !== false) {
        echo $file->getPathname() . " (" . $file->getSize() . " bytes)\n";
    }
}

echo "\n=== storage exists? ===\n";
echo "storage/ " . (is_dir($base . '/storage') ? 'YES' : 'NO') . "\n";
echo "storage/logs/ " . (is_dir($base . '/storage/logs') ? 'YES' : 'NO') . "\n";
