<?php
$src = '/home/m/mastak97/gbsale.ru/public_html/2026_07_30_011244_add_type_to_stores_table.php';
$dst = '/home/m/mastak97/gbsale.ru/api/database/migrations/2026_07_30_011244_add_type_to_stores_table.php';

if (!file_exists($src)) {
    echo "source not found: $src\n";
    exit(1);
}

$content = file_get_contents($src);
$bytes = file_put_contents($dst, $content);
chmod($dst, 0644);
echo "copied $bytes bytes to $dst\n";
