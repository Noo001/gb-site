<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

const BASE_DIR = '/home/m/mastak97/gbsale.ru/api';
$file = BASE_DIR . '/app/Models/Store.php';
echo "exists: " . (file_exists($file) ? 'yes' : 'no') . "\n";
echo "readable: " . (is_readable($file) ? 'yes' : 'no') . "\n";
if (is_readable($file)) {
    $content = file_get_contents($file);
    echo "bytes: " . strlen($content) . "\n";
    echo "has resolveType: " . (str_contains($content, 'resolveType') ? 'yes' : 'no') . "\n";
}
