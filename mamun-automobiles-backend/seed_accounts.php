<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

// Create Categories
$catSalary = Category::firstOrCreate(['slug' => 'expense-salary'], ['name' => 'Salary Expense', 'type' => 'expense']);
$catUtility = Category::firstOrCreate(['slug' => 'expense-utility'], ['name' => 'Utility Bills', 'type' => 'expense']);
$catRent = Category::firstOrCreate(['slug' => 'expense-rent'], ['name' => 'Workshop Rent', 'type' => 'expense']);
$catSales = Category::firstOrCreate(['slug' => 'income-sales'], ['name' => 'Service Sales', 'type' => 'income']);

// Create Accounts
$cashAccount = Account::firstOrCreate(['name' => 'Main Cash'], ['type' => 'cash', 'balance' => 0]);
$bankAccount = Account::firstOrCreate(['name' => 'City Bank Ltd'], ['type' => 'bank', 'account_no' => '123456789', 'balance' => 0]);

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Transaction::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

function createTx($account, $type, $amount, $catId, $date, $desc) {
    $tx = Transaction::create([
        'account_id' => $account->id,
        'type' => $type,
        'amount' => $amount,
        'category_id' => $catId,
        'date' => $date,
        'description' => $desc,
        'payment_method' => $account->type === 'cash' ? 'cash' : 'bank_transfer'
    ]);
    $account->balance += ($type === 'income' ? $amount : -$amount);
    $account->save();
}

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

$cashAccount->balance = 50000;
$cashAccount->save();
$bankAccount->balance = 200000;
$bankAccount->save();

createTx($cashAccount, 'expense', 15000, $catSalary->id, $today, 'Monthly Salary for Mechanic John');
createTx($bankAccount, 'expense', 5000, $catUtility->id, $yesterday, 'Electricity Bill Payment');
createTx($bankAccount, 'expense', 25000, $catRent->id, $today, 'Monthly Workshop Rent');
createTx($cashAccount, 'income', 12000, $catSales->id, $today, 'Daily service cash collection');

echo 'Accounting Demo data seeded successfully!';

