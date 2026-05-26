<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\JobCard;
use App\Models\JobCardItem;
use App\Models\User;
use App\Models\Part;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class SimulateWorkflow extends Command
{
    protected $signature = 'simulate:workflow';
    protected $description = 'Simulate the full end-to-end service job workflow';

    public function handle()
    {
        DB::transaction(function () {
            // 1. Customer
            $this->info("1. Creating Customer...");
            $customer = Customer::create([
                'name' => 'John Doe Workflow',
                'phone' => '01999999999',
                'email' => 'john.workflow@example.com',
                'address' => 'Workflow Address',
            ]);
            $this->line("Created Customer: {$customer->name} (ID: {$customer->id})");

            // 2. Vehicle
            $this->info("\n2. Creating Vehicle...");
            $vehicle = Vehicle::create([
                'customer_id' => $customer->id,
                'make' => 'Toyota',
                'model' => 'Corolla',
                'year' => '2015',
                'license_plate' => 'DHA-11-2233',
                'vin' => 'NZE141-1234567',
            ]);
            $this->line("Created Vehicle: {$vehicle->make} {$vehicle->model} (ID: {$vehicle->id})");

            // 3. Service Job & 4. Mechanic Assignment
            $this->info("\n3. Creating Service Job (Job Card) & 4. Mechanic Assignment...");
            // Get a mechanic user
            $mechanic = User::firstOrCreate(
                ['email' => 'mechanic@mamunerp.com'],
                ['name' => 'Expert Mechanic', 'password' => bcrypt('password')]
            );
            $mechanic->assignRole('Technician');

            $jobCard = JobCard::create([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'assigned_mechanic_id' => $mechanic->id,
                'service_date' => now(),
                'service_status' => \App\Enums\ServiceStatus::PENDING,
                'complaint' => 'Engine noise and oil leak',
                'estimated_cost' => 5000,
            ]);
            $this->line("Created Job Card (Mechanic: {$mechanic->name})");

            // 5. Parts Usage
            $this->info("\n5. Parts Usage...");
            $part = Part::first(); // Grab a random part, e.g., Oil Filter
            if(!$part) {
                $this->error("No parts found in DB to use!");
                return;
            }
            $part->update(['stock_quantity' => 100]); // Ensure stock

            $jobCardItem = JobCardItem::create([
                'job_card_id' => $jobCard->id,
                'part_id' => $part->id,
                'description' => 'Replaced ' . $part->name,
                'quantity' => 2,
                'unit_price' => $part->sale_price,
                'total_price' => 2 * $part->sale_price,
            ]);
            $invoiceTotal = 2 * $part->sale_price;
            $this->line("Added Part: {$part->name} x 2 (Cost: {$invoiceTotal})");

            // Update Job Card status to completed
            $jobCard->update(['service_status' => \App\Enums\ServiceStatus::COMPLETED]);
            $this->line("Job Card marked as completed.");

            // 6. Invoice
            $this->info("\n6. Generating Invoice...");
            // Simulate InvoiceController logic
            $invoice = Invoice::create([
                'invoice_number' => 'INV-TEST-' . time(),
                'customer_id' => $customer->id,
                'parts_total' => $invoiceTotal,
                'service_total' => 0,
                'grand_total' => $invoiceTotal,
                'paid_amount' => 0,
                'due_amount' => $invoiceTotal,
                'payment_status' => 'unpaid',
            ]);
            $this->line("Generated Invoice: {$invoice->invoice_number} (Total: {$invoiceTotal})");

            // 7. Payment & 8. Inventory Deduction & 9. Accounts Entry
            $this->info("\n7. Payment, 8. Inventory Deduction, 9. Accounts Entry...");
            
            // Inventory Deduction usually happens on Invoice creation or Part allocation
            $part->decrement('stock_quantity', 2);
            $this->line("Inventory Deducted: {$part->name} stock reduced by 2. New stock: {$part->stock_quantity}");

            // Accounts Entry
            $account = Account::first();
            if(!$account) {
                $account = Account::create(['name' => 'Main Cash', 'type' => 'cash', 'balance' => 0, 'status' => 'active', 'account_no' => 'CASH-01']);
            }
            
            // Payment
            $invoice->update([
                'paid_amount' => $invoiceTotal,
                'due_amount' => 0,
                'payment_status' => 'paid',
            ]);

            $transaction = Transaction::create([
                'account_id' => $account->id,
                'type' => 'credit',
                'amount' => $invoiceTotal,
                'reference_type' => get_class($invoice),
                'reference_id' => $invoice->id,
                'description' => 'Payment for Invoice ' . $invoice->invoice_number,
                'date' => now(),
            ]);
            $account->increment('balance', $invoiceTotal);
            $this->line("Payment received. Accounts Entry added: +{$invoiceTotal} to {$account->name} (New Balance: {$account->balance})");

            // 10. Analytics Update
            $this->info("\n10. Analytics Update...");
            $this->line("Analytics are generated dynamically via queries. Let's show current month's sales:");
            $salesThisMonth = Invoice::whereMonth('created_at', now()->month)->sum('grand_total');
            $this->line("Total Sales This Month: {$salesThisMonth}");

            $this->info("\n✅ Workflow Completed Successfully!");
        });
    }
}
