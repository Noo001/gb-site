<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bot:rebuild-index')->hourly();
Schedule::command('1c:process-queue')->everyMinute();

// Shared-хостинг: нет постоянного queue worker, обрабатываем очередь пачками по cron
Schedule::command('queue:work --stop-when-empty --max-jobs=50 --sleep=3 --tries=3')
    ->everyMinute()
    ->withoutOverlapping();

