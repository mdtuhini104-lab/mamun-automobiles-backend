<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
app()->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Vehicle;
use App\Models\Customer;

$customer = Customer::first();
if (!$customer) {
    $customer = Customer::create(['name' => 'Demo Customer', 'phone' => '1234567890', 'email' => 'demo@example.com']);
}

Vehicle::updateOrCreate(['license_plate' => 'DHK-12-3456'], [
    'customer_id' => $customer->id,
    'make' => 'Toyota',
    'model' => 'Corolla',
    'year' => 2020,
    'vin' => 'JT123456789',
    'engine_number' => '1NZ-FE1234',
    'color' => 'White',
    'fuel_type' => 'Petrol',
]);

Vehicle::updateOrCreate(['license_plate' => 'CTG-99-8877'], [
    'customer_id' => $customer->id,
    'make' => 'Honda',
    'model' => 'Civic',
    'year' => 2022,
    'vin' => 'JH123456789',
    'engine_number' => 'L15B7890',
    'color' => 'Black',
    'fuel_type' => 'Hybrid',
]);
echo 'Vehicles seeded successfully.';
