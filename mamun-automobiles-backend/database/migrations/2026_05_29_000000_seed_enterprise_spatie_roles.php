<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        // Forget cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Seed 'Admin' Role
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $allPermissions = Permission::pluck('name')->toArray();
        $adminPermissions = array_filter($allPermissions, function ($p) {
            return !str_starts_with($p, 'saas_admin') && !str_starts_with($p, 'settings');
        });
        $admin->syncPermissions($adminPermissions);

        // 2. Seed 'Staff' Role
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $staffPermissions = [
            'dashboard.view',
            'job_cards.view', 'job_cards.create', 'job_cards.edit',
            'inventory.view',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.print',
            'customers.view', 'customers.create', 'customers.edit',
            'purchases.view', 'purchases.create', 'purchases.edit'
        ];
        // Ensure only existing permissions are assigned
        $existingStaffPerms = Permission::whereIn('name', $staffPermissions)->pluck('name')->toArray();
        $staff->syncPermissions($existingStaffPerms);

        // 3. Seed 'Viewer' Role
        $viewer = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
        $viewerPermissions = [
            'dashboard.view',
            'job_cards.view',
            'inventory.view',
            'invoices.view',
            'customers.view',
            'purchases.view'
        ];
        $existingViewerPerms = Permission::whereIn('name', $viewerPermissions)->pluck('name')->toArray();
        $viewer->syncPermissions($existingViewerPerms);
    }

    public function down(): void
    {
        // Leave seeded roles intact to prevent breaking production access on rollback
    }
};
