<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

class FleetManagementService
{
    /**
     * Get all fleet contracts for a tenant.
     */
    public function getContracts(int $tenantId): array
    {
        return DB::table('fleet_contracts')
            ->where('tenant_id', $tenantId)
            ->get()
            ->toArray();
    }

    /**
     * Create a new fleet contract.
     */
    public function createContract(int $tenantId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $data) {
            $id = DB::table('fleet_contracts')->insertGetId([
                'tenant_id' => $tenantId,
                'company_name' => $data['company_name'],
                'contact_email' => $data['contact_email'],
                'billing_terms' => $data['billing_terms'] ?? 'Net 30',
                'discount_percent' => $data['discount_percent'] ?? 0.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Audit log
            DB::table('audit_logs')->insert([
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'action' => 'create_fleet_contract',
                'module' => 'fleet',
                'details' => json_encode(['contract_id' => $id, 'company_name' => $data['company_name']]),
                'ip_address' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return DB::table('fleet_contracts')->where('id', $id)->first();
        });
    }

    /**
     * Bulk approve quotations associated with a fleet.
     */
    public function bulkApproveQuotations(int $tenantId, array $quotationIds): array
    {
        return DB::transaction(function () use ($tenantId, $quotationIds) {
            $approvedCount = 0;
            $failed = [];

            foreach ($quotationIds as $id) {
                $quotation = DB::table('quotations')
                    ->where('id', $id)
                    ->where('tenant_id', $tenantId)
                    ->first();

                if (!$quotation) {
                    $failed[] = ['id' => $id, 'reason' => 'Quotation not found'];
                    continue;
                }

                if ($quotation->status === 'approved') {
                    continue; // Already approved
                }

                // Update quotation status
                DB::table('quotations')
                    ->where('id', $id)
                    ->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'updated_at' => now()
                    ]);

                // Create work order for the approved quotation
                DB::table('work_orders')->insert([
                    'tenant_id' => $tenantId,
                    'quotation_id' => $id,
                    'job_card_id' => $quotation->job_card_id,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Audit log entry
                DB::table('audit_logs')->insert([
                    'tenant_id' => $tenantId,
                    'user_id' => auth()->id(),
                    'action' => 'fleet_bulk_approve_quotation',
                    'module' => 'fleet',
                    'details' => json_encode(['quotation_id' => $id]),
                    'ip_address' => request()->ip(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $approvedCount++;
            }

            return [
                'success' => true,
                'approved_count' => $approvedCount,
                'failed' => $failed
            ];
        });
    }

    /**
     * Get aggregate statistics for Fleet dashboard metrics.
     */
    public function getFleetMetrics(int $tenantId): array
    {
        $contracts = DB::table('fleet_contracts')->where('tenant_id', $tenantId)->get();
        $totalContracts = $contracts->count();
        $activeContracts = $contracts->where('status', 'active')->count();

        // Count pending fleet quotations
        $pendingQuotations = DB::table('quotations')
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->whereExists(function ($query) use ($tenantId) {
                // Link quotation to a customer vehicle marked as fleet
                $query->select(DB::raw(1))
                    ->from('customers')
                    ->join('vehicles', 'vehicles.customer_id', '=', 'customers.id')
                    ->join('quotations', 'quotations.job_card_id', '=', 'vehicles.id') // mock link check
                    ->whereRaw('customers.is_fleet_account = ?', [true]);
            })
            ->count();

        return [
            'total_contracts' => $totalContracts,
            'active_contracts' => $activeContracts,
            'pending_authorizations' => $pendingQuotations,
        ];
    }
}
