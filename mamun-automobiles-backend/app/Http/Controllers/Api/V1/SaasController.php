<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TenantProvisioningService;
use App\Services\SubscriptionService;
use App\Services\SaaSPluginManager;
use Illuminate\Http\Request;
use Exception;

class SaasController extends Controller
{
    protected $provisioningService;
    protected $subscriptionService;
    protected $pluginManager;

    public function __construct(
        TenantProvisioningService $provisioningService,
        SubscriptionService $subscriptionService,
        SaaSPluginManager $pluginManager
    ) {
        $this->provisioningService = $provisioningService;
        $this->subscriptionService = $subscriptionService;
        $this->pluginManager = $pluginManager;
    }

    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain' => 'required|string|alpha_dash|max:100|unique:tenants,domain',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'plan_name' => 'nullable|string|in:base,pro,enterprise',
        ]);

        try {
            $result = $this->provisioningService->provision($request->all());
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to provision tenant: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate(Request $request)
    {
        return response()->json($this->subscriptionService->activatePlan($request->input('tenant_id'), $request->input('plan_id')));
    }

    /**
     * Get all plugins and installation status for the current tenant.
     */
    public function getPlugins(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        try {
            $plugins = $this->pluginManager->getPlugins($tenantId);
            return response()->json([
                'success' => true,
                'data' => $plugins
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve marketplace plugins: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Install or uninstall a marketplace plugin.
     */
    public function togglePlugin(Request $request)
    {
        $request->validate([
            'plugin_key' => 'required|string',
            'install' => 'required|boolean',
        ]);

        $tenantId = $request->user()->tenant_id;
        $pluginKey = $request->input('plugin_key');
        $install = $request->input('install');

        try {
            $this->pluginManager->togglePlugin($tenantId, $pluginKey, $install);
            return response()->json([
                'success' => true,
                'message' => $install ? 'Plugin successfully installed.' : 'Plugin uninstalled.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
