<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

DB::table('automations')->delete();
DB::table('ai_insights')->delete();

// Seed Automations
DB::table('automations')->insert([
    [
        'tenant_id' => 1,
        'name' => 'Auto-Assign Mechanic to Engine Jobs',
        'event_trigger' => 'job_card.created',
        'conditions' => json_encode(['service_category' => 'Engine']),
        'actions' => json_encode(['assign_to' => 'best_available_mechanic', 'notify' => true]),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'tenant_id' => 1,
        'name' => 'Low Oil Filter Stock Alert',
        'event_trigger' => 'inventory.updated',
        'conditions' => json_encode(['item_type' => 'Oil Filter', 'stock' => '< 10']),
        'actions' => json_encode(['trigger_reorder_workflow' => true, 'notify_manager' => true]),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]
]);

// Seed AI Insights
DB::table('ai_insights')->insert([
    [
        'tenant_id' => 1,
        'type' => 'efficiency',
        'title' => 'Mechanic Efficiency Drop Detected',
        'description' => 'John Doe has taken 20% longer on Engine Repair tasks this week compared to historical averages.',
        'data_payload' => json_encode(['mechanic_id' => 5, 'avg_time' => '4h', 'current_time' => '4.8h']),
        'severity' => 'warning',
        'is_resolved' => false,
        'created_at' => now()->subHours(2),
        'updated_at' => now(),
    ],
    [
        'tenant_id' => 1,
        'type' => 'inventory_prediction',
        'title' => 'Upcoming Peak Demand: Brake Pads',
        'description' => 'Based on historical trends and upcoming booked appointments, brake pad consumption will spike next week.',
        'data_payload' => json_encode(['predicted_demand' => '+45%', 'confidence' => '92%']),
        'severity' => 'info',
        'is_resolved' => false,
        'created_at' => now()->subDays(1),
        'updated_at' => now(),
    ]
]);

echo 'AI Automation Demo Data Seeded!';

