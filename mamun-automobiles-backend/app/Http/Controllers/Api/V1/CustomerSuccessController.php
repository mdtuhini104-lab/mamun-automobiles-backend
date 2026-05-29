<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CustomerSuccessService;
use Illuminate\Support\Facades\DB;
use Exception;

class CustomerSuccessController extends Controller
{
    protected $successService;

    public function __construct(CustomerSuccessService $successService)
    {
        $this->successService = $successService;
    }

    /**
     * Get success indicators for the current tenant.
     */
    public function getMetrics(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        try {
            $health = $this->successService->getTenantHealthScore($tenantId);
            $onboarding = $this->successService->getOnboardingCompletionRate($tenantId);

            return response()->json([
                'success' => true,
                'data' => [
                    'health' => $health,
                    'onboarding_rate' => $onboarding
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve success telemetry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get historical health trends snapshots for a tenant.
     */
    public function getHistory(Request $request, ?int $id = null)
    {
        $tenantId = $id ?? $request->user()->tenant_id;

        // Isolation Check: Standard users cannot query other tenants' trends
        if ($tenantId !== $request->user()->tenant_id && !$request->user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to tenant historical success logs.'
            ], 403);
        }

        try {
            $days = (int) $request->query('days', 30);
            $history = $this->successService->getHealthHistory($tenantId, $days);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to query historical success trends: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve global success profiles of all tenants (restricted to Super Admins).
     */
    public function getGlobalSuccessDashboard(Request $request)
    {
        if (!$request->user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super Admin permissions required.'
            ], 403);
        }

        try {
            $tenants = DB::table('tenants')->get();
            $profiles = [];

            foreach ($tenants as $t) {
                $health = $this->successService->getTenantHealthScore($t->id);
                $onboarding = $this->successService->getOnboardingCompletionRate($t->id);

                $profiles[] = [
                    'tenant_id' => $t->id,
                    'company_name' => $t->company_name,
                    'domain' => $t->domain,
                    'plan' => $t->subscription_plan,
                    'health' => $health,
                    'onboarding_rate' => $onboarding
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $profiles
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to compile global success profiles: ' . $e->getMessage()
            ], 500);
        }
    }
}
