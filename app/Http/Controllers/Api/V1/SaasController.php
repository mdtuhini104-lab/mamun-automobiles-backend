<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SaasController extends Controller
{
    protected $tenantService;
    protected $subscriptionService;

    public function __construct(TenantService $tenantService, SubscriptionService $subscriptionService)
    {
        $this->tenantService = $tenantService;
        $this->subscriptionService = $subscriptionService;
    }

    public function register(Request $request)
    {
        return response()->json($this->tenantService->provisionTenant($request->all()));
    }

    public function activate(Request $request)
    {
        return response()->json($this->subscriptionService->activatePlan($request->input('tenant_id'), $request->input('plan_id')));
    }
}
