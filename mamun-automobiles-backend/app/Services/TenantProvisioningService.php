<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Tenant;
use Exception;

class TenantProvisioningService
{
    /**
     * Provision a brand new tenant with workspace configurations, 
     * default branch, and accounting chart settings.
     */
    public function provision(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Tenant Record
            $tenantId = DB::table('tenants')->insertGetId([
                'company_name' => $data['company_name'],
                'domain' => $data['subdomain'] . '.mamunerp.com',
                'status' => 'trial',
                'subscription_plan' => $data['plan_name'] ?? 'base',
                'subscription_ends_at' => now()->addDays(14), // 14-day trial default
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 2. Create Default Branch
            $branchId = DB::table('branches')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => 'Main Branch (' . $data['company_name'] . ')',
                'address' => $data['address'] ?? 'Primary Location Address',
                'phone' => $data['phone'] ?? '+000000000',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 3. Seed Default Chart of Accounts (COA) for Double-Entry bookkeeping
            $coaAccounts = [
                ['account_code' => "COA-{$tenantId}-1000", 'account_name' => 'Cash in Hand (Main Drawer)', 'account_type' => 'asset'],
                ['account_code' => "COA-{$tenantId}-1010", 'account_name' => 'Bank Account (Primary)', 'account_type' => 'asset'],
                ['account_code' => "COA-{$tenantId}-2000", 'account_name' => 'Accounts Payable (Suppliers)', 'account_type' => 'liability'],
                ['account_code' => "COA-{$tenantId}-3000", 'account_name' => 'Retained Earnings / Equity', 'account_type' => 'equity'],
                ['account_code' => "COA-{$tenantId}-4000", 'account_name' => 'Automotive Repairs Service Revenue', 'account_type' => 'revenue'],
                ['account_code' => "COA-{$tenantId}-5000", 'account_name' => 'Inventory Spare Parts Cost', 'account_type' => 'expense'],
                ['account_code' => "COA-{$tenantId}-5010", 'account_name' => 'Workshop Labor & Payroll Expense', 'account_type' => 'expense'],
            ];

            foreach ($coaAccounts as $account) {
                DB::table('accounts_chart')->insert([
                    'tenant_id' => $tenantId,
                    'account_code' => $account['account_code'],
                    'account_name' => $account['account_name'],
                    'account_type' => $account['account_type'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // 4. Seeding Tenant-Specific Feature Limits/Configuration Defaults if any
            DB::table('tenant_feature_limits')->insert([
                'tenant_id' => $tenantId,
                'feature_name' => 'branch_limit',
                'limit_value' => ($data['plan_name'] ?? 'base') === 'enterprise' ? 999 : (($data['plan_name'] ?? 'base') === 'pro' ? 3 : 1),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return [
                'success' => true,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'domain' => $data['subdomain'] . '.mamunerp.com',
                'company_name' => $data['company_name'],
            ];
        });
    }
}
