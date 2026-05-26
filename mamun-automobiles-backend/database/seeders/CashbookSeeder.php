<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cashbook;
use App\Models\CashbookTransaction;
use App\Models\DailyClosing;

class CashbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainCash = Cashbook::create([
            'name' => 'Main Cash Drawer',
            'type' => 'main_cash',
            'opening_balance' => 50000.00,
            'current_balance' => 55000.00,
            'status' => true
        ]);

        $bankAccount = Cashbook::create([
            'name' => 'Islami Bank BD',
            'type' => 'bank',
            'opening_balance' => 200000.00,
            'current_balance' => 205000.00,
            'status' => true
        ]);

        CashbookTransaction::create([
            'cashbook_id' => $mainCash->id,
            'reference_type' => 'invoice_payment',
            'transaction_type' => 'income',
            'category' => 'sales',
            'amount' => 5000.00,
            'balance_after' => 55000.00,
            'description' => 'Payment for Invoice #1024',
        ]);

        CashbookTransaction::create([
            'cashbook_id' => $bankAccount->id,
            'reference_type' => 'invoice_payment',
            'transaction_type' => 'income',
            'category' => 'sales',
            'amount' => 5000.00,
            'balance_after' => 205000.00,
            'description' => 'Bank Transfer for Invoice #1025',
        ]);

        DailyClosing::create([
            'cashbook_id' => $mainCash->id,
            'opening_balance' => 50000.00,
            'total_income' => 5000.00,
            'total_expense' => 0.00,
            'closing_balance' => 55000.00,
            'system_balance' => 55000.00,
            'closing_notes' => 'Day closed perfectly.',
            'closed_at' => now()->subDay(),
        ]);
    }
}
