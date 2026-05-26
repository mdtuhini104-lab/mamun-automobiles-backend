<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Seed Departments
$deptWorkshop = DB::table('departments')->insertGetId(['name' => 'Workshop', 'description' => 'Service and Repair', 'created_at' => now()]);
$deptAdmin = DB::table('departments')->insertGetId(['name' => 'Administration', 'description' => 'Office Staff', 'created_at' => now()]);
$deptAccounts = DB::table('departments')->insertGetId(['name' => 'Accounts', 'description' => 'Finance', 'created_at' => now()]);

// Seed Designations
$desigMechanic = DB::table('designations')->insertGetId(['name' => 'Senior Mechanic', 'department_id' => $deptWorkshop, 'created_at' => now()]);
$desigManager = DB::table('designations')->insertGetId(['name' => 'Service Manager', 'department_id' => $deptWorkshop, 'created_at' => now()]);
$desigCashier = DB::table('designations')->insertGetId(['name' => 'Cashier', 'department_id' => $deptAccounts, 'created_at' => now()]);

// Seed Shifts
$shiftMorning = DB::table('shifts')->insertGetId(['name' => 'Morning Shift', 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'created_at' => now()]);
$shiftEvening = DB::table('shifts')->insertGetId(['name' => 'Evening Shift', 'start_time' => '12:00:00', 'end_time' => '20:00:00', 'created_at' => now()]);

// Assign to existing users
User::where('email', 'mechanic@mamunerp.com')->update(['department_id' => $deptWorkshop, 'designation_id' => $desigMechanic, 'shift_id' => $shiftMorning]);
User::where('email', 'manager@mamunerp.com')->update(['department_id' => $deptWorkshop, 'designation_id' => $desigManager, 'shift_id' => $shiftMorning]);
User::where('email', 'cashier@mamunerp.com')->update(['department_id' => $deptAccounts, 'designation_id' => $desigCashier, 'shift_id' => $shiftEvening]);

// Seed Attendance for Mechanic
$mechanicId = User::where('email', 'mechanic@mamunerp.com')->value('id');

for ($i = 1; $i <= 10; $i++) {
    $date = date('Y-m-d', strtotime('-' . $i . ' days'));
    DB::table('attendances')->insert([
        'user_id' => $mechanicId,
        'date' => $date,
        'check_in' => '08:05:00',
        'check_out' => '16:05:00',
        'status' => 'present',
        'late_minutes' => 5,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

// Seed Payroll for Mechanic
DB::table('payrolls')->insert([
    'user_id' => $mechanicId,
    'month' => date('m', strtotime('-1 month')),
    'year' => date('Y', strtotime('-1 month')),
    'basic_salary' => 30000,
    'overtime_amount' => 2000,
    'bonus' => 0,
    'deductions' => 0,
    'net_salary' => 32000,
    'status' => 'paid',
    'payment_date' => date('Y-m-d'),
    'created_at' => now(),
    'updated_at' => now()
]);

echo 'HR Demo Data Seeded!';

