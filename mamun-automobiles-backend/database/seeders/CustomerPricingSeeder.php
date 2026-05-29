<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Part;
use App\Models\CustomerPricing;

class CustomerPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = Customer::first();
        $part = Part::first();

        if ($customer && $part) {
            // Seed a negotiated price for a product (part)
            CustomerPricing::firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'part_id' => $part->id,
                ],
                [
                    'custom_price' => $part->sale_price * 0.90, // 10% discount on this part
                    'effective_date' => now()->toDateString(),
                    'notes' => 'Negotiated corporate rate (10% off list price)',
                ]
            );

            // Seed a negotiated labor rate for a service
            CustomerPricing::firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'labor_service_name' => 'Engine Scan',
                ],
                [
                    'custom_labor_rate' => 1500.00, // Fixed cost for Engine Scan
                    'effective_date' => now()->toDateString(),
                    'notes' => 'Flat labor rate for scan diagnostics',
                ]
            );
        }
    }
}
