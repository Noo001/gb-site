<?php
echo 'HTTP_COOKIE: ' . ($_SERVER['HTTP_COOKIE'] ?? 'NOT SET') . "\n";
echo 'raw site_access: ' . ($_COOKIE['site_access'] ?? 'NOT SET') . "\n";
if (isset($_COOKIE['site_access'])) {
    echo 'len: ' . strlen($_COOKIE['site_access']) . "\n";
    echo 'last10: ' . substr($_COOKIE['site_access'], -10) . "\n";
}
