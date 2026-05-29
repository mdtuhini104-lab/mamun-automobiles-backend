<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = [
    'users', 'customers', 'vehicles', 'job_cards', 'invoices', 
    'parts', 'transactions', 'appointments', 'quotations', 
    'work_orders', 'work_order_consumptions', 'customer_pricings',
    'suppliers', 'purchases', 'stock_adjustments', 'workshop_bays',
    'employees', 'departments', 'designations', 'shifts'
];

foreach ($tables as $t) {
    if (Schema::hasTable($t)) {
        echo "$t: " . implode(', ', Schema::getColumnListing($t)) . "\n\n";
    } else {
        echo "$t: NOT FOUND\n\n";
    }
}
