<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FleetManagementService;
use Exception;

class FleetController extends Controller
{
    protected $fleetService;

    public function __construct(FleetManagementService $fleetService)
    {
        $this->fleetService = $fleetService;
    }

    /**
     * List all fleet contracts.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        try {
            $contracts = $this->fleetService->getContracts($tenantId);
            return response()->json([
                'success' => true,
                'data' => $contracts
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load fleet contracts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new fleet contract.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'billing_terms' => 'nullable|string|max:100',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $tenantId = $request->user()->tenant_id;
        try {
            $contract = $this->fleetService->createContract($tenantId, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Fleet contract registered successfully.',
                'data' => $contract
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register fleet contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve quotations.
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'quotation_ids' => 'required|array',
            'quotation_ids.*' => 'integer',
        ]);

        $tenantId = $request->user()->tenant_id;
        try {
            $result = $this->fleetService->bulkApproveQuotations($tenantId, $request->input('quotation_ids'));
            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$result['approved_count']} approvals.",
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get aggregate statistics for the Fleet Dashboard.
     */
    public function metrics(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        try {
            $metrics = $this->fleetService->getFleetMetrics($tenantId);
            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve fleet metrics: ' . $e->getMessage()
            ], 500);
        }
    }
}
