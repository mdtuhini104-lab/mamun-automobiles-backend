<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Appointment;
use App\Models\CustomerActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

// 1. Update Customers with CRM data
$customers = Customer::all();
foreach ($customers as $customer) {
    $customer->update([
        'password' => Hash::make('password123'),
        'tag' => ['VIP', 'Corporate', 'Regular'][rand(0, 2)],
        'loyalty_points' => rand(100, 1000)
    ]);
    
    // Seed some activities
    CustomerActivity::create([
        'customer_id' => $customer->id,
        'type' => 'account_created',
        'description' => 'Customer account registered in CRM portal.'
    ]);
}

// 2. Seed Appointments
$mechanics = User::role('Technician')->get();
if ($customers->count() > 0 && $mechanics->count() > 0) {
    for ($i = 0; $i < 10; $i++) {
        $customer = $customers->random();
        $vehicle = Vehicle::where('customer_id', $customer->id)->first();
        
        Appointment::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle ? $vehicle->id : null,
            'mechanic_id' => $mechanics->random()->id,
            'appointment_date' => Carbon::now()->addDays(rand(1, 10))->format('Y-m-d'),
            'appointment_time' => sprintf('%02d:00:00', rand(9, 16)),
            'service_type' => ['Full Servicing', 'Oil Change', 'Brake Inspection'][rand(0, 2)],
            'status' => ['pending', 'confirmed'][rand(0, 1)],
            'remarks' => 'Please check the engine noise.'
        ]);
    }
}

echo 'CRM Demo Data Seeded!';

