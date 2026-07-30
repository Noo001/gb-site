<?php
$files = [
    '../api/database/migrations/2026_07_30_011244_add_type_to_stores_table.php',
    '../api/app/Models/Store.php',
    '../api/app/Filament/Pages/ListRecords.php',
];
foreach ($files as $f) {
    $full = __DIR__ . '/' . $f;
    echo "$f => exists=" . (file_exists($full) ? 'YES' : 'NO') . " size=" . (file_exists($full) ? filesize($full) : 0) . "\n";
}
