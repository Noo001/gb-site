<?php

require __DIR__ . '/../api/vendor/autoload.php';

$app = require_once __DIR__ . '/../api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput([
        'command' => 'migrate',
        '--force' => true,
        '--path' => 'database/migrations/2026_07_30_011244_add_type_to_stores_table.php',
    ]),
    new Symfony\Component\Console\Output\StreamOutput(fopen('php://stdout', 'w'))
);

$kernel->terminate($input, $status);

exit($status);
