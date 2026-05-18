<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('git:push', function () {
    $this->info('Running git add .');
    $output = shell_exec('git add . 2>&1');
    $this->info($output);

    $this->info('Running git commit');
    $output = shell_exec('git commit -m "Complete Mamun Automobiles ERP Backend Phase 1" 2>&1');
    $this->info($output);

    $this->info('Running git push');
    $output = shell_exec('git push -u origin main 2>&1');
    $this->info($output);
})->purpose('Run git commands');
