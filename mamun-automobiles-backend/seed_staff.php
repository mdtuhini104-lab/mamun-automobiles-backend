<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

// Clear cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Create permissions
$permissions = [
    'dashboard.view',
    'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
    'vehicle.view', 'vehicle.create', 'vehicle.edit', 'vehicle.delete',
    'job_card.view', 'job_card.create', 'job_card.edit', 'job_card.delete',
    'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.delete',
    'purchase.view', 'purchase.create', 'purchase.edit', 'purchase.delete',
    'stock.view', 'stock.adjust',
    'account.view', 'account.create', 'account.edit',
    'transaction.view', 'transaction.create',
    'report.view',
    'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
    'pos.access'
];

foreach ($permissions as $perm) {
    Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
}

// Create Roles & Assign Permissions
$roleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
$roleSuperAdmin->syncPermissions(Permission::all());

$roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
$roleAdmin->syncPermissions(Permission::all()); // For demo, almost all

$roleManager = Role::firstOrCreate(['name' => 'Manager']);
$roleManager->syncPermissions([
    'dashboard.view', 'customer.view', 'customer.create', 'customer.edit',
    'vehicle.view', 'vehicle.create', 'vehicle.edit',
    'job_card.view', 'job_card.create', 'job_card.edit',
    'invoice.view', 'invoice.create', 'invoice.edit',
    'purchase.view', 'purchase.create',
    'stock.view', 'stock.adjust',
    'report.view', 'staff.view'
]);

$roleCashier = Role::firstOrCreate(['name' => 'Cashier']);
$roleCashier->syncPermissions([
    'dashboard.view', 'invoice.view', 'invoice.create', 'transaction.view', 'transaction.create', 'pos.access'
]);

$roleTechnician = Role::firstOrCreate(['name' => 'Technician']);
$roleTechnician->syncPermissions([
    'dashboard.view', 'job_card.view', 'vehicle.view'
]);

$roleAccountant = Role::firstOrCreate(['name' => 'Accountant']);
$roleAccountant->syncPermissions([
    'dashboard.view', 'account.view', 'account.create', 'account.edit', 
    'transaction.view', 'transaction.create', 'report.view', 'invoice.view', 'purchase.view'
]);

$roleInventoryManager = Role::firstOrCreate(['name' => 'Inventory Manager']);
$roleInventoryManager->syncPermissions([
    'dashboard.view', 'stock.view', 'stock.adjust', 'purchase.view', 'purchase.create'
]);

// Helper to create user
function createStaff($name, $email, $roleName, $phone, $salary) {
    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'password' => Hash::make('password123'),
            'phone' => $phone,
            'salary' => $salary,
            'joining_date' => date('Y-m-d', strtotime('-1 month')),
            'is_active' => true
        ]
    );
    $user->syncRoles([$roleName]);
    return $user;
}

createStaff('System Admin', 'admin@mamunerp.com', 'Super Admin', '01711000000', 100000);
createStaff('Manager Hasan', 'manager@mamunerp.com', 'Manager', '01711000001', 50000);
createStaff('Cashier Rahim', 'cashier@mamunerp.com', 'Cashier', '01711000002', 25000);
createStaff('Mechanic John', 'mechanic@mamunerp.com', 'Technician', '01711000003', 30000);
createStaff('Accountant Ali', 'accountant@mamunerp.com', 'Accountant', '01711000004', 40000);
createStaff('Inventory Kabir', 'inventory@mamunerp.com', 'Inventory Manager', '01711000005', 35000);

echo 'Roles, Permissions and Demo Staff seeded successfully!';

