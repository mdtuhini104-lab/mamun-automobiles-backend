<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('run:migrate', function () {
    $this->info('Running migrate');
    $output = shell_exec('php artisan migrate --force 2>&1');
    $this->info($output);
})->purpose('Run migrations');

// Daily automatic database backup at 12:00 AM (midnight)
Schedule::command('backup:run')->dailyAt('00:00');
