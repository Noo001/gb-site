<?php
echo "__DIR__ = " . __DIR__ . "\n";
echo "realpath(__DIR__) = " . realpath(__DIR__) . "\n";
$paths = [
    'vendor/autoload.php',
    '../vendor/autoload.php',
    '../../api/vendor/autoload.php',
    '../api/vendor/autoload.php',
    'api/vendor/autoload.php',
    '../bootstrap/app.php',
    '../../api/bootstrap/app.php',
];
foreach ($paths as $p) {
    $full = __DIR__ . '/' . $p;
    echo "$p => " . $full . " exists=" . (file_exists($full) ? 'YES' : 'NO') . "\n";
}
