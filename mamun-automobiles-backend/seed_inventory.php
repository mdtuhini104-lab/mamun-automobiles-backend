<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Supplier;
use App\Models\Part;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

$catEngine = Category::firstOrCreate(['name' => 'Engine Parts'], ['slug' => 'engine-parts']);
$catBody = Category::firstOrCreate(['name' => 'Body Parts'], ['slug' => 'body-parts']);

$supplier = Supplier::firstOrCreate(['email' => 'parts@supplier.com'], [
    'name' => 'Auto Parts Wholesaler',
    'phone' => '1234567890',
    'address' => '123 Parts Street',
    'status' => 'active'
]);

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Part::truncate();
InventoryTransaction::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

$part1 = Part::create([
    'name' => 'Synthetic Engine Oil 5W-40',
    'sku' => 'OIL-5W40',
    'barcode' => '8901234567890',
    'brand' => 'Mobil 1',
    'cost_price' => 3000,
    'sale_price' => 4500,
    'stock_quantity' => 20,
    'low_stock_threshold' => 5,
    'category_id' => $catEngine->id,
    'rack_location' => 'Rack A1',
    'unit_type' => 'ltr'
]);

$part2 = Part::create([
    'name' => 'Front Bumper',
    'sku' => 'BUMP-F01',
    'barcode' => '8901234567891',
    'brand' => 'OEM',
    'cost_price' => 12000,
    'sale_price' => 15000,
    'stock_quantity' => 2,
    'low_stock_threshold' => 1,
    'category_id' => $catBody->id,
    'rack_location' => 'Floor B',
    'unit_type' => 'pcs'
]);

$part3 = Part::create([
    'name' => 'Ceramic Brake Pads',
    'sku' => 'BRK-C01',
    'barcode' => '8901234567892',
    'brand' => 'Brembo',
    'cost_price' => 4500,
    'sale_price' => 6000,
    'stock_quantity' => 50,
    'low_stock_threshold' => 10,
    'category_id' => $catEngine->id,
    'rack_location' => 'Rack C3',
    'unit_type' => 'set'
]);

foreach ([$part1, $part2, $part3] as $p) {
    InventoryTransaction::create([
        'part_id' => $p->id,
        'type' => 'in',
        'quantity' => $p->stock_quantity,
        'reference_type' => 'adjustment',
        'unit_cost' => $p->cost_price,
        'notes' => 'Initial Stock Setup'
    ]);
}

echo 'Inventory Demo data seeded successfully!';

