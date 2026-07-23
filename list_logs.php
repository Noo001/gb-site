<?php
require '/home/m/mastak97/gbsale.ru/api/vendor/autoload.php';
$app = require_once '/home/m/mastak97/gbsale.ru/api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$logs = App\Models\IntegrationLog::orderBy('id', 'desc')->limit(5)->get();
foreach ($logs as $l) {
    echo 'id=' . $l->id . ' | ' . $l->created_at . ' | ' . $l->method . ' ' . $l->path . ' | code=' . ($l->response_code ?? 'NULL') . ' | body=' . substr($l->response_body ?? '', 0, 200) . PHP_EOL;
}
