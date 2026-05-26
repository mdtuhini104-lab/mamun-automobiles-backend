<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear tables to make it idempotent
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\DB::table('role_has_permissions')->truncate();
        \Illuminate\Support\Facades\DB::table('model_has_roles')->truncate();
        \Illuminate\Support\Facades\DB::table('model_has_permissions')->truncate();
        \Illuminate\Support\Facades\DB::table('roles')->truncate();
        \Illuminate\Support\Facades\DB::table('permissions')->truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // Define modules and their actions
        $modules = [
            'dashboard' => ['view'],
            'analytics' => ['view', 'export'],
            'customers' => ['view', 'create', 'edit', 'delete'],
            'vehicles' => ['view', 'create', 'edit', 'delete'],
            'job_cards' => ['view', 'create', 'edit', 'delete', 'approve', 'print'],
            'appointments' => ['view', 'create', 'edit', 'delete'],
            'invoices' => ['view', 'create', 'edit', 'delete', 'print'],
            'accounts' => ['view', 'create', 'edit', 'delete', 'approve'],
            'inventory' => ['view', 'create', 'edit', 'delete'],
            'purchases' => ['view', 'create', 'edit', 'delete', 'approve'],
            'payrolls' => ['view', 'create', 'edit', 'delete', 'approve'],
            'attendances' => ['view', 'create', 'edit', 'delete'],
            'staff' => ['view', 'create', 'edit', 'delete'],
            'ai_operations' => ['view', 'execute'],
            'saas_admin' => ['view', 'edit'],
            'settings' => ['view', 'edit'],
        ];

        $allPermissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}";
                $allPermissions[] = $permissionName;
                Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                    'module' => $module,
                    'description' => "Can {$action} {$module}"
                ]);
            }
        }

        // Forget cached permissions again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Super Admin role
        $superAdmin = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        // Super Admin has all permissions via Gate::before rule

        // Create Manager role
        $manager = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
        $managerPermissions = array_filter($allPermissions, function($p) {
            return !str_starts_with($p, 'saas_admin') && !str_starts_with($p, 'settings');
        });
        $manager->givePermissionTo($managerPermissions);

        // Create Technician role
        $technician = Role::create(['name' => 'Technician', 'guard_name' => 'web']);
        $technician->givePermissionTo([
            'dashboard.view',
            'job_cards.view', 'job_cards.edit',
            'inventory.view'
        ]);

        // Create Cashier role
        $cashier = Role::create(['name' => 'Cashier', 'guard_name' => 'web']);
        $cashier->givePermissionTo([
            'dashboard.view',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.print',
            'customers.view'
        ]);

        // Create Store Manager role
        $storeManager = Role::create(['name' => 'Store Manager', 'guard_name' => 'web']);
        $storeManager->givePermissionTo([
            'dashboard.view',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.approve'
        ]);
    }
}
