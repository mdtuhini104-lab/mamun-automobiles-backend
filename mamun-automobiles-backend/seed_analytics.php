<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use App\Models\JobCard;
use App\Models\User;
use App\Models\Part;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$accountId = DB::table('accounts')->first()->id ?? DB::table('accounts')->insertGetId(['name' => 'Main Cash', 'type' => 'cash', 'balance' => 0, 'created_at' => now()]);

// 1. Transactions over the last 6 months
$types = ['income', 'expense'];
for ($i = 0; $i < 60; $i++) {
    $monthSub = rand(0, 5);
    $date = Carbon::now()->subMonths($monthSub)->subDays(rand(0, 28));
    $type = $types[array_rand($types)];
    
    $amount = $type === 'income' ? rand(5000, 50000) : rand(1000, 20000);
    
    DB::table('transactions')->insert([
        'date' => $date,
        'type' => $type,
        'account_id' => $accountId,
        'category_id' => null,
        'amount' => $amount,
        'payment_method' => 'cash',
        'reference_type' => null,
        'reference_id' => null,
        'description' => 'Analytics seeded transaction',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

// 2. Mechanic Productivity
$mechanics = User::role('Mechanic')->get();
if ($mechanics->count() > 0) {
    for ($i = 0; $i < 20; $i++) {
        $mechanic = $mechanics->random();
        DB::table('job_cards')->insert([
            'customer_id' => DB::table('customers')->first()->id ?? 1,
            'vehicle_id' => DB::table('vehicles')->first()->id ?? 1,
            'assigned_mechanic_id' => $mechanic->id,
            'complaint' => 'Analytics seeded job',
            'service_status' => 'completed',
            'service_date' => date('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

// 3. Low Stock Items
DB::table('parts')->updateOrInsert(
    ['sku' => 'ANA-LOW-1'],
    ['name' => 'Brake Pads Model X', 'sku' => 'ANA-LOW-1', 'stock_quantity' => 2, 'low_stock_threshold' => 10, 'cost_price' => 500, 'sale_price' => 800, 'created_at' => now(), 'updated_at' => now()]
);
DB::table('parts')->updateOrInsert(
    ['sku' => 'ANA-LOW-2'],
    ['name' => 'Engine Oil 5W-30', 'sku' => 'ANA-LOW-2', 'stock_quantity' => 5, 'low_stock_threshold' => 20, 'cost_price' => 1500, 'sale_price' => 1800, 'created_at' => now(), 'updated_at' => now()]
);

echo 'Analytics Demo Data Seeded!';

