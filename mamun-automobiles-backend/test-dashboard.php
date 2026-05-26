<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Calling getDashboardData()...\n";
    $service = app(App\Services\DashboardService::class);
    $data = $service->getDashboardData();
    echo "SUCCESS!\n";
    echo json_encode($data, JSON_THROW_ON_ERROR);
} catch (\Exception $e) {
    echo "ERROR EXCEPTION:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
