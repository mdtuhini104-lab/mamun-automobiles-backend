<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Part;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Account;
use App\Models\Transaction;
use App\Enums\PurchaseStatus;
use App\Enums\PaymentStatus;
use App\Enums\SupplierStatus;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users (Manager, Cashier)
        $manager = User::firstOrCreate(
            ['email' => 'manager@mamunerp.com'],
            ['name' => 'Mamun Manager', 'password' => Hash::make('password123')]
        );
        $manager->assignRole('Manager');

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@mamunerp.com'],
            ['name' => 'Mamun Cashier', 'password' => Hash::make('password123')]
        );
        $cashier->assignRole('Cashier');

        // 2. Create Account
        $account = Account::firstOrCreate(
            ['account_no' => 'MAMUN-CASH-01'],
            ['name' => 'Main Cash Drawer', 'type' => 'cash', 'balance' => 100000, 'status' => 'active']
        );

        // 3. Create Categories
        $categories = ['Engine Parts', 'Body Parts', 'Electrical', 'Suspension', 'Filters', 'Lubricants', 'Brakes'];
        $catIds = [];
        foreach ($categories as $cat) {
            $catRecord = Category::firstOrCreate(['name' => $cat], ['slug' => \Str::slug($cat), 'status' => 'active']);
            $catIds[] = $catRecord->id;
        }

        // 4. Seed 50 Parts (Realistic Bangladeshi auto parts)
        $partsData = [
            // Engine Parts
            ['Toyota Corolla Piston Ring Set', 'ENG-001', 'Toyota', 2500, 3200, 10, $catIds[0]],
            ['Toyota Allion Spark Plug', 'ENG-002', 'Denso', 400, 600, 50, $catIds[0]],
            ['Honda Civic Timing Belt', 'ENG-003', 'Honda', 1200, 1800, 20, $catIds[0]],
            ['Hyundai Tucson Water Pump', 'ENG-004', 'Hyundai', 3500, 4500, 5, $catIds[0]],
            ['Nissan Sunny Fuel Injector', 'ENG-005', 'Nissan', 4500, 6000, 8, $catIds[0]],
            ['Mitsubishi Lancer Radiator', 'ENG-006', 'Mitsubishi', 5500, 7500, 4, $catIds[0]],
            ['Toyota Premio Alternator', 'ENG-007', 'Toyota', 6000, 8500, 6, $catIds[0]],
            ['Mazda Axela Starter Motor', 'ENG-008', 'Mazda', 4800, 6500, 5, $catIds[0]],
            // Body Parts
            ['Toyota Aqua Front Bumper', 'BDY-001', 'Toyota', 8000, 12000, 3, $catIds[1]],
            ['Honda Vezel Headlight Assembly (L)', 'BDY-002', 'Honda', 15000, 22000, 2, $catIds[1]],
            ['Toyota Fielder Rear Taillight', 'BDY-003', 'Toyota', 4500, 6500, 6, $catIds[1]],
            ['Nissan X-Trail Side Mirror (R)', 'BDY-004', 'Nissan', 3000, 4500, 4, $catIds[1]],
            ['Hyundai Sonata Front Grill', 'BDY-005', 'Hyundai', 3500, 5000, 3, $catIds[1]],
            ['Kia Sportage Fender (L)', 'BDY-006', 'Kia', 6000, 8500, 2, $catIds[1]],
            ['Toyota Prado Hood/Bonnet', 'BDY-007', 'Toyota', 20000, 30000, 1, $catIds[1]],
            // Electrical
            ['Amaron Battery 12V 45Ah', 'ELE-001', 'Amaron', 4500, 5500, 15, $catIds[2]],
            ['Bosch Horn Set', 'ELE-002', 'Bosch', 800, 1200, 30, $catIds[2]],
            ['Pioneer Car Audio Receiver', 'ELE-003', 'Pioneer', 8500, 11000, 4, $catIds[2]],
            ['Toyota Noah Power Window Switch', 'ELE-004', 'Toyota', 1500, 2500, 10, $catIds[2]],
            ['Denso AC Compressor', 'ELE-005', 'Denso', 12000, 16000, 3, $catIds[2]],
            // Suspension
            ['KYB Front Shock Absorber', 'SUS-001', 'KYB', 3500, 4800, 12, $catIds[3]],
            ['Toyota Axio Lower Arm', 'SUS-002', 'Toyota', 2800, 4000, 8, $catIds[3]],
            ['Honda CR-V Tie Rod End', 'SUS-003', 'Honda', 1200, 1800, 15, $catIds[3]],
            ['Nissan Bluebird Stabilizer Link', 'SUS-004', 'Nissan', 800, 1300, 20, $catIds[3]],
            ['Hyundai Elantra Steering Rack', 'SUS-005', 'Hyundai', 15000, 21000, 2, $catIds[3]],
            // Filters
            ['Toyota Genuine Oil Filter (Corolla/Axio)', 'FLT-001', 'Toyota', 350, 500, 100, $catIds[4]],
            ['Honda Genuine Air Filter (Civic)', 'FLT-002', 'Honda', 800, 1200, 50, $catIds[4]],
            ['Nissan Cabin AC Filter', 'FLT-003', 'Nissan', 400, 700, 80, $catIds[4]],
            ['Hyundai Fuel Filter (Tucson)', 'FLT-004', 'Hyundai', 1500, 2200, 25, $catIds[4]],
            ['Sakura Oil Filter (Universal)', 'FLT-005', 'Sakura', 250, 400, 150, $catIds[4]],
            // Lubricants
            ['Mobil 1 Synthetic Engine Oil 5W-40 (4L)', 'LUB-001', 'Mobil', 3800, 4500, 40, $catIds[5]],
            ['Castrol Magnatec 10W-40 (4L)', 'LUB-002', 'Castrol', 2800, 3500, 60, $catIds[5]],
            ['Toyota Genuine Motor Oil 5W-30 (4L)', 'LUB-003', 'Toyota', 3200, 4000, 50, $catIds[5]],
            ['Shell Helix Ultra 5W-40 (4L)', 'LUB-004', 'Shell', 3500, 4200, 35, $catIds[5]],
            ['Honda Genuine Engine Oil 0W-20 (4L)', 'LUB-005', 'Honda', 3000, 3800, 30, $catIds[5]],
            ['Liqui Moly MoS2 Anti-Friction 10W-40', 'LUB-006', 'Liqui Moly', 4200, 5200, 20, $catIds[5]],
            ['Motul 8100 X-cess 5W-40', 'LUB-007', 'Motul', 4500, 5500, 15, $catIds[5]],
            ['Caltex Havoline ProDS 5W-40', 'LUB-008', 'Caltex', 3100, 3900, 25, $catIds[5]],
            ['Toyota ATF Type T-IV (4L)', 'LUB-009', 'Toyota', 3600, 4500, 20, $catIds[5]],
            ['Honda ATF DW-1 (4L)', 'LUB-010', 'Honda', 3800, 4800, 15, $catIds[5]],
            ['Prestone Antifreeze Coolant (3.78L)', 'LUB-011', 'Prestone', 1200, 1800, 40, $catIds[5]],
            ['Toyota Genuine Super Long Life Coolant', 'LUB-012', 'Toyota', 2500, 3200, 30, $catIds[5]],
            ['WD-40 Multi-Use Product (400ml)', 'LUB-013', 'WD-40', 350, 500, 100, $catIds[5]],
            ['Wurth Brake Cleaner (500ml)', 'LUB-014', 'Wurth', 400, 600, 80, $catIds[5]],
            ['Abro Carb & Choke Cleaner', 'LUB-015', 'Abro', 250, 400, 120, $catIds[5]],
            // Brakes
            ['Akebono Front Brake Pad (Corolla)', 'BRK-001', 'Akebono', 2500, 3500, 30, $catIds[6]],
            ['Nisshinbo Rear Brake Shoe (Axio)', 'BRK-002', 'Nisshinbo', 1800, 2500, 25, $catIds[6]],
            ['Honda Genuine Brake Pad (Civic)', 'BRK-003', 'Honda', 3500, 5000, 20, $catIds[6]],
            ['Brembo Front Brake Rotor', 'BRK-004', 'Brembo', 6000, 8500, 10, $catIds[6]],
            ['Seiken Brake Fluid DOT4 (500ml)', 'BRK-005', 'Seiken', 450, 650, 50, $catIds[6]],
        ];

        $parts = [];
        foreach ($partsData as $index => $pd) {
            $parts[] = Part::create([
                'name' => $pd[0],
                'sku' => $pd[1],
                'brand' => $pd[2],
                'cost_price' => $pd[3],
                'sale_price' => $pd[4],
                'stock_quantity' => 0, // Will be updated by purchases
                'low_stock_threshold' => 5,
                'category_id' => $pd[6]
            ]);
        }

        // 5. Seed 20 Suppliers
        $suppliersData = [
            'Dhaka Auto Parts', 'Rahim Motors', 'Nitol Motors Spares', 'Navana Parts Center', 'Rangs Workshop Supply',
            'Uttara Motors Spares', 'Bengal Auto Accessories', 'Karim & Sons Spares', 'Hossain Traders', 'Molla Auto Point',
            'Islam Brothers Motors', 'Chittagong Spares Hub', 'Sylhet Auto Point', 'Mirpur Parts Corner', 'Mohakhali Spares Zone',
            'Dhaleshwari Auto Parts', 'Bhai Bhai Motors', 'Unique Auto Spares', 'Global Motors Supply', 'National Auto Parts'
        ];
        $suppliers = [];
        foreach ($suppliersData as $index => $name) {
            $suppliers[] = Supplier::create([
                'name' => $name,
                'email' => 'contact'.($index+1).'@'.\Str::slug($name).'.com',
                'phone' => '0171'.str_pad($index, 7, '0', STR_PAD_LEFT),
                'address' => 'Dhaka, Bangladesh',
                'company_name' => $name.' Ltd.',
                'status' => SupplierStatus::ACTIVE,
            ]);
        }

        // 6. Seed 30 Customers
        $customersData = [
            'Kamal Hossain', 'Tariqul Islam', 'Abdur Rahman', 'Sayed Ahmed', 'Rafiqul Islam',
            'Nazmul Hasan', 'Faisal Mahmud', 'Arifur Rahman', 'Shahidul Alam', 'Zahirul Islam',
            'Mehedi Hasan', 'Imran Hossain', 'Habibur Rahman', 'Ashiqur Rahman', 'Aminul Islam',
            'Sajjad Hossain', 'Tanvir Ahmed', 'Mahmudul Hasan', 'Rashedul Islam', 'Nizam Uddin',
            'Jashim Uddin', 'Mostafa Kamal', 'Shafiqul Islam', 'Golam Rabbani', 'Rubel Hossain',
            'Sohel Rana', 'Faruk Ahmed', 'Monir Hossain', 'Masud Rana', 'Iqbal Hossain'
        ];
        $customers = [];
        foreach ($customersData as $index => $name) {
            $customers[] = Customer::create([
                'name' => $name,
                'phone' => '0181'.str_pad($index, 7, '0', STR_PAD_LEFT),
                'email' => \Str::slug($name).'@example.com',
                'address' => 'Dhaka, Bangladesh',
                'balance' => 0,
            ]);
        }

        // 7. Generate Purchases (Adds to stock)
        for ($i = 0; $i < 15; $i++) {
            $supplier = $suppliers[array_rand($suppliers)];
            $numItems = rand(3, 8);
            
            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'purchase_no' => 'PUR-'.date('Ym').'-'.str_pad($i+1, 4, '0', STR_PAD_LEFT),
                'purchase_date' => now()->subDays(rand(1, 30)),
                'total_amount' => 0,
                'paid_amount' => 0,
                'due_amount' => 0,
                'payment_status' => PaymentStatus::DUE,
                'status' => PurchaseStatus::RECEIVED, // Changed from APPROVED
            ]);

            $total = 0;
            for ($j = 0; $j < $numItems; $j++) {
                $part = $parts[array_rand($parts)];
                $qty = rand(10, 50);
                $unitPrice = $part->cost_price;
                $lineTotal = $qty * $unitPrice;
                
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'part_id' => $part->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ]);

                // Update stock manually for approved purchase
                $part->increment('stock_quantity', $qty);
                $total += $lineTotal;
            }

            // Decide payment
            $paid = rand(0, 1) == 1 ? $total : rand(0, $total);
            $due = $total - $paid;
            $paymentStatus = $due == 0 ? PaymentStatus::PAID : ($paid > 0 ? PaymentStatus::PARTIAL : PaymentStatus::DUE);

            $purchase->update([
                'total_amount' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $paymentStatus,
            ]);

            // Create Transaction if paid
            if ($paid > 0) {
                Transaction::create([
                    'account_id' => $account->id,
                    'type' => 'debit', // Money going out
                    'amount' => $paid,
                    'reference_type' => 'App\Models\Purchase',
                    'reference_id' => $purchase->id,
                    'description' => 'Payment for Purchase ' . $purchase->purchase_no,
                    'date' => $purchase->purchase_date,
                ]);
                $account->decrement('balance', $paid);
            }
        }

        // 8. Generate Invoices (Reduces stock)
        for ($i = 0; $i < 20; $i++) {
            $customer = $customers[array_rand($customers)];
            $numItems = rand(1, 5);
            
            $invoice = Invoice::create([
                'invoice_number' => 'INV-'.date('Ym').'-'.str_pad($i+1, 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'parts_total' => 0,
                'service_total' => 0, // Simplified, ignoring service for now
                'grand_total' => 0,
                'paid_amount' => 0,
                'due_amount' => 0,
                'payment_status' => 'unpaid',
            ]);

            $total = 0;
            for ($j = 0; $j < $numItems; $j++) {
                $part = tap($parts[array_rand($parts)])->refresh();
                $qty = rand(1, 4);
                // Ensure we don't go negative stock for realism, though some systems allow it
                if ($part->stock_quantity >= $qty) {
                    $unitPrice = $part->sale_price;
                    $lineTotal = $qty * $unitPrice;
                    
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'part_id' => $part->id,
                        'description' => $part->name,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ]);

                    $part->decrement('stock_quantity', $qty);
                    $total += $lineTotal;
                }
            }

            // Decide payment
            $paid = rand(0, 10) > 2 ? $total : rand(0, $total); // 80% fully paid
            $due = $total - $paid;
            $paymentStatus = $due == 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            $invoice->update([
                'parts_total' => $total,
                'grand_total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $paymentStatus,
            ]);

            // Create Transaction if paid
            if ($paid > 0) {
                Transaction::create([
                    'account_id' => $account->id,
                    'type' => 'credit', // Money coming in
                    'amount' => $paid,
                    'reference_type' => 'App\Models\Invoice',
                    'reference_id' => $invoice->id,
                    'description' => 'Payment for Invoice ' . $invoice->invoice_number,
                    'date' => now()->subDays(rand(1, 15)),
                ]);
                $account->increment('balance', $paid);
                
                // If it's a due amount, realistically customer balance might increase, but we'll skip complex AR logic for seed demo
            }
        }

    }
}
