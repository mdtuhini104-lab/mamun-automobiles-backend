<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\WorkshopBay;
use App\Models\User;
use App\Models\Employee;
use App\Models\Part;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class OnboardPilotBranch extends Command
{
    protected $signature = 'app:onboard-pilot-branch {--name=Mirpur Pilot Workshop}';
    protected $description = 'Onboard a new real-world pilot workshop branch, including bays, users, employee profiles, and inventory parts.';

    public function handle()
    {
        $branchName = $this->option('name');
        $this->info("Initializing real-world pilot onboarding sequence for: {$branchName}...");

        DB::transaction(function () use ($branchName) {
            // 1. Get or create Tenant
            $tenant = Tenant::firstOrCreate(
                ['domain' => 'mamun.erp'],
                ['company_name' => 'Mamun Automobiles']
            );
            $this->line("Tenant established: {$tenant->company_name} (ID: {$tenant->id})");

            // 2. Create Branch
            $branch = Branch::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => $branchName
                ]
            );
            $this->line("Branch established: {$branch->name} (ID: {$branch->id})");

            // 3. Create 3 Workshop Bays
            $bays = [
                ['name' => 'Bay-01 Express', 'code' => 'B01-EXP', 'max_vehicle_capacity' => 1],
                ['name' => 'Bay-02 Diagnostics', 'code' => 'B02-DIAG', 'max_vehicle_capacity' => 1],
                ['name' => 'Bay-03 Heavy Repairs', 'code' => 'B03-HVY', 'max_vehicle_capacity' => 2],
            ];

            foreach ($bays as $bayData) {
                $bay = WorkshopBay::where('tenant_id', $tenant->id)
                    ->where('branch_id', $branch->id)
                    ->where('code', $bayData['code'])
                    ->first();

                if (!$bay) {
                    $bay = new WorkshopBay();
                    $bay->tenant_id = $tenant->id;
                    $bay->branch_id = $branch->id;
                    $bay->code = $bayData['code'];
                    $bay->name = $bayData['name'];
                    $bay->max_vehicle_capacity = $bayData['max_vehicle_capacity'];
                    $bay->current_load = 0;
                    $bay->status = 'active';
                    $bay->save();
                }
                $this->line("Workshop Bay seeded: {$bay->name} (Code: {$bay->code})");
            }

            // 4. Seeding Workforce staff users and matching Employee profiles
            $staff = [
                [
                    'name' => 'Mirpur Manager',
                    'email' => 'mirpur.manager@mamunerp.com',
                    'phone' => '01711122331',
                    'role' => 'Manager',
                    'salary' => 55000,
                    'code' => 'EMP-MGR-M01',
                    'first' => 'Mirpur',
                    'last' => 'Manager'
                ],
                [
                    'name' => 'Mirpur Lead Tech',
                    'email' => 'mirpur.tech1@mamunerp.com',
                    'phone' => '01711122332',
                    'role' => 'Technician',
                    'salary' => 35000,
                    'code' => 'EMP-TCH-T01',
                    'first' => 'Mirpur',
                    'last' => 'TechOne'
                ],
                [
                    'name' => 'Mirpur Helper Tech',
                    'email' => 'mirpur.tech2@mamunerp.com',
                    'phone' => '01711122333',
                    'role' => 'Technician',
                    'salary' => 22000,
                    'code' => 'EMP-TCH-T02',
                    'first' => 'Mirpur',
                    'last' => 'TechTwo'
                ],
                [
                    'name' => 'Mirpur Cashier',
                    'email' => 'mirpur.cashier@mamunerp.com',
                    'phone' => '01711122334',
                    'role' => 'Cashier',
                    'salary' => 25000,
                    'code' => 'EMP-CSH-C01',
                    'first' => 'Mirpur',
                    'last' => 'Cashier'
                ],
            ];

            foreach ($staff as $item) {
                $user = User::where('email', $item['email'])->first();
                if (!$user) {
                    $user = new User();
                    $user->email = $item['email'];
                    $user->name = $item['name'];
                    $user->password = Hash::make('pilotpassword123');
                    $user->phone = $item['phone'];
                    $user->tenant_id = $tenant->id;
                    $user->branch_id = $branch->id;
                    $user->salary = $item['salary'];
                    $user->save();
                }

                // Spatie roles allocation
                $role = Role::firstOrCreate(['name' => $item['role'], 'guard_name' => 'web']);
                $user->syncRoles([$item['role']]);

                // Matching Employee Profile
                $employee = Employee::where('user_id', $user->id)->first();
                if (!$employee) {
                    $employee = new Employee();
                    $employee->user_id = $user->id;
                    $employee->tenant_id = $tenant->id;
                    $employee->branch_id = $branch->id;
                    $employee->employee_code = $item['code'];
                    $employee->first_name = $item['first'];
                    $employee->last_name = $item['last'];
                    $employee->phone = $item['phone'];
                    $employee->salary = $item['salary'];
                    $employee->joining_date = now();
                    $employee->status = 'active';
                    $employee->availability_status = 'active';
                    $employee->save();
                }

                $this->line("Staff and employee profile created: {$user->name} (Role: {$item['role']})");
            }

            // 5. Seed basic inventory parts
            $parts = [
                ['name' => 'Synthetic Engine Oil 5W-30', 'sku' => 'OIL-5W30-SYN', 'brand' => 'Mobil 1', 'cost_price' => 2800, 'sale_price' => 3500, 'stock' => 50, 'low' => 10],
                ['name' => 'Premium Oil Filter', 'sku' => 'FLT-OIL-PREM', 'brand' => 'Bosch', 'cost_price' => 550, 'sale_price' => 800, 'stock' => 30, 'low' => 5],
                ['name' => 'Platinum Spark Plug', 'sku' => 'PLG-SPK-PLAT', 'brand' => 'NGK', 'cost_price' => 900, 'sale_price' => 1200, 'stock' => 24, 'low' => 8],
                ['name' => 'Brake Pad Front Set', 'sku' => 'PAD-BRK-FRNT', 'brand' => 'Akebono', 'cost_price' => 3800, 'sale_price' => 4500, 'stock' => 15, 'low' => 4],
            ];

            foreach ($parts as $partData) {
                $part = Part::where('sku', $partData['sku'])->first();
                if (!$part) {
                    $part = new Part();
                    $part->tenant_id = $tenant->id;
                    $part->branch_id = $branch->id;
                    $part->sku = $partData['sku'];
                    $part->name = $partData['name'];
                    $part->brand = $partData['brand'];
                    $part->cost_price = $partData['cost_price'];
                    $part->sale_price = $partData['sale_price'];
                    $part->stock_quantity = $partData['stock'];
                    $part->low_stock_threshold = $partData['low'];
                    $part->save();
                }
                $this->line("Inventory Part seeded: {$partData['name']} (SKU: {$partData['sku']})");
            }

            $this->info("\n✅ Pilot Onboarding sequence completed successfully!");
        });
    }
}
