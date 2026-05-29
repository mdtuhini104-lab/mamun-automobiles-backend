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

// Daily automatic tenant health snapshots compilation at 12:30 AM
Schedule::command('saas:record-health-snapshots')->dailyAt('00:30');

// Daily automatic tenant inactivity scans at 1:00 AM
Schedule::command('saas:check-tenant-inactivity')->dailyAt('01:00');

// Daily automatic subscription renewal reminders scanning at 1:30 AM
Schedule::command('saas:send-renewal-reminders')->dailyAt('01:30');

