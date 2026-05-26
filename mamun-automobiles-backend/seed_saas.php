<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

// Remove foreign key constraint temporarily
DB::statement('SET FOREIGN_KEY_CHECKS=0;');

// Nullify tenant_id on tables before deleting tenants to avoid cascading deletions of users
$tables = ['users', 'customers', 'vehicles', 'job_cards', 'invoices', 'parts', 'transactions', 'appointments', 'branches'];
foreach ($tables as $table) {
    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
        DB::table($table)->update(['tenant_id' => null]);
    }
}

DB::table('branches')->delete();
DB::table('tenants')->delete();

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// 1. Create Main Tenant
$tenantId = DB::table('tenants')->insertGetId([
    'company_name' => 'Mamun Automobiles Global',
    'domain' => 'mamun.erp.test',
    'status' => 'active',
    'subscription_ends_at' => \Carbon\Carbon::now()->addYears(1),
    'subscription_plan' => 'enterprise',
    'created_at' => now(),
    'updated_at' => now(),
]);

// 2. Create another tenant for testing
DB::table('tenants')->insert([
    'company_name' => 'AutoFix Motors (Trial)',
    'domain' => 'autofix.erp.test',
    'status' => 'trial',
    'subscription_ends_at' => \Carbon\Carbon::now()->addDays(14),
    'subscription_plan' => 'basic',
    'created_at' => now(),
    'updated_at' => now(),
]);

// 3. Create Branches for Main Tenant
$branchMainId = DB::table('branches')->insertGetId([
    'tenant_id' => $tenantId,
    'name' => 'Dhaka Headquarters',
    'phone' => '+8801700000001',
    'address' => 'Mirpur, Dhaka',
    'created_at' => now(),
    'updated_at' => now(),
]);

$branchSubId = DB::table('branches')->insertGetId([
    'tenant_id' => $tenantId,
    'name' => 'Chittagong Branch',
    'phone' => '+8801700000002',
    'address' => 'Agrabad, Chittagong',
    'created_at' => now(),
    'updated_at' => now(),
]);

// 4. Update existing records to belong to Main Tenant & Main Branch
foreach ($tables as $table) {
    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
        DB::table($table)->update(['tenant_id' => $tenantId]);
    }
}

// 5. Create a Super Admin Role and assign it
use Spatie\Permission\Models\Role;
$superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
$user = User::where('email', 'admin@mamunerp.com')->first();
if ($user) {
    $user->assignRole($superAdminRole);
}

echo 'SaaS & Multi-Branch Demo Data Seeded!';

