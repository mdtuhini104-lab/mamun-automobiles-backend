<?php

// Boot Laravel application environment for script execution
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "==================================================\n";
echo "Mamun Automobiles ERP Load & Stress Test Simulator\n";
echo "==================================================\n";

// Dynamically fetch or create a valid tenant and branch to bypass constraint checks
$tenantId = DB::table('tenants')->value('id');
if (!$tenantId) {
    $tenantId = DB::table('tenants')->insertGetId([
        'company_name' => 'Load Test Company',
        'domain' => 'loadtest.mamunerp.com',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

$branchId = DB::table('branches')->where('tenant_id', $tenantId)->value('id');
if (!$branchId) {
    $branchId = DB::table('branches')->insertGetId([
        'tenant_id' => $tenantId,
        'name' => 'Main Test Branch',
        'address' => 'Test Location Address',
        'phone' => '+0000000',
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

// 1. Stress check DB insertions (Job Card creation load)
echo "\n[1/3] Simulating concurrent Job Card creations (100 operations)...\n";
$startInsert = microtime(true);

DB::beginTransaction();
try {
    for ($i = 0; $i < 100; $i++) {
        $customerId = DB::table('customers')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => "Stress Test User {$i}",
            'phone' => '+017000000' . sprintf('%02d', $i),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $vehicleId = DB::table('vehicles')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'license_plate' => "STRESS-" . sprintf('%03d', $i),
            'make' => 'Toyota',
            'model' => 'Corolla',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('job_cards')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'service_status' => 'pending',
            'odometer_reading' => 12000,
            'complaint' => 'AC cooling performance stress test diagnosis required',
            'service_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    DB::commit();
    $endInsert = microtime(true);
    $timeInsert = round($endInsert - $startInsert, 3);
    echo "✓ 100 Job Cards successfully created inside database transaction.\n";
    echo "Total duration: {$timeInsert} seconds (" . round(100 / $timeInsert, 1) . " inserts/sec).\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ Database insertion stress check failed: " . $e->getMessage() . "\n";
}

// 2. Stress check event dispatching (Notification bursts)
echo "\n[2/3] Simulating background notification burst (100 dispatches)...\n";
$startQueue = microtime(true);
$dispatched = 0;
for ($i = 0; $i < 100; $i++) {
    try {
        // Enqueue mock notification event
        DB::table('communication_logs')->insert([
            'tenant_id' => $tenantId,
            'recipient_phone' => '+0170000000',
            'message_body' => "Stress test alert packet: #{$i}",
            'channel' => 'sms',
            'status' => 'queued',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $dispatched++;
    } catch (\Exception $e) {
        echo "Failed to enqueue notification: " . $e->getMessage() . "\n";
        break;
    }
}
$endQueue = microtime(true);
$timeQueue = round($endQueue - $startQueue, 3);
echo "✓ Enqueued {$dispatched} outgoing notifications in queue.\n";
echo "Total duration: {$timeQueue} seconds (" . round($dispatched / $timeQueue, 1) . " queues/sec).\n";

// 3. Trace slow query incidents
echo "\n[3/3] Querying system monitoring for query delays exceeding SLA (>1s)...\n";
$slowQueries = DB::table('system_health_alerts')->where('alert_type', 'slow_query')->count();
echo "Total logged slow queries: {$slowQueries}\n";

echo "\n==================================================\n";
echo "Simulation Finished Successfully!\n";
echo "==================================================\n";
