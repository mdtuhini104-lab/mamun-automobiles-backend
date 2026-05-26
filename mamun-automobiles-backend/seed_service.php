<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\JobCard;
use App\Models\JobCardItem;
use App\Models\Part;
use App\Models\Category;
use App\Models\User;
use App\Services\InvoiceService;

// Create category and parts
$cat = Category::firstOrCreate(['name' => 'Engine Parts'], ['description' => 'Engine related parts', 'slug' => 'engine-parts']);
$part1 = Part::firstOrCreate(['sku' => 'PRT-001'], ['name' => 'Oil Filter', 'brand' => 'Bosch', 'cost_price' => 150, 'sale_price' => 250, 'stock_quantity' => 50, 'low_stock_threshold' => 10, 'category_id' => $cat->id]);
$part2 = Part::firstOrCreate(['sku' => 'PRT-002'], ['name' => 'Brake Pad', 'brand' => 'Brembo', 'cost_price' => 1200, 'sale_price' => 1800, 'stock_quantity' => 20, 'low_stock_threshold' => 5, 'category_id' => $cat->id]);

$customer = Customer::first();
$vehicle = Vehicle::where('customer_id', $customer->id)->first();
$mechanic = User::first();

// Create Job Card
$job = JobCard::create([
    'customer_id' => $customer->id,
    'vehicle_id' => $vehicle->id,
    'assigned_mechanic_id' => $mechanic->id,
    'complaint' => 'Brakes squeaking and oil needs change.',
    'diagnosis' => 'Brake pads worn out. Engine oil dirty.',
    'service_status' => 'completed',
    'estimated_cost' => 2500,
    'final_cost' => 500, // Service/Labor charge
    'service_date' => now(),
    'delivery_date' => now()->addDay(),
    'notes' => 'Customer requested quick delivery.',
]);

// Add Items to Job Card
JobCardItem::create(['job_card_id' => $job->id, 'part_id' => $part1->id, 'quantity' => 1, 'unit_price' => $part1->sale_price, 'total_price' => $part1->sale_price]);
JobCardItem::create(['job_card_id' => $job->id, 'part_id' => $part2->id, 'quantity' => 2, 'unit_price' => $part2->sale_price, 'total_price' => $part2->sale_price * 2]);

// Generate Invoice
$invoiceService = app(InvoiceService::class);
$invoice = $invoiceService->generateInvoiceFromJobCard($job->id);

echo 'Demo data seeded successfully!';

