<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SubscriptionEngineService;
use App\Services\FeatureFlagService;
use Illuminate\Support\Facades\DB;
use Exception;

class SubscriptionController extends Controller
{
    protected $subscriptionEngine;
    protected $featureFlagService;

    public function __construct(
        SubscriptionEngineService $subscriptionEngine,
        FeatureFlagService $featureFlagService
    ) {
        $this->subscriptionEngine = $subscriptionEngine;
        $this->featureFlagService = $featureFlagService;
    }

    /**
     * Get the active subscription details and feature limits.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $subscription = $this->subscriptionEngine->getSubscription($tenantId);

        // Fetch billing history/invoices
        $invoices = DB::table('tenant_subscriptions')
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        $planName = $subscription ? $subscription->plan_name : 'base';
        
        $features = [
            'view_dashboard' => $this->featureFlagService->isEnabled('view_dashboard', $tenantId),
            'manage_invoices' => $this->featureFlagService->isEnabled('manage_invoices', $tenantId),
            'ai_quota_assistant' => $this->featureFlagService->isEnabled('ai_quota_assistant', $tenantId),
            'ai_predictive_maintenance' => $this->featureFlagService->isEnabled('ai_predictive_maintenance', $tenantId),
            'white_label' => $this->featureFlagService->isEnabled('white_label', $tenantId),
            'fleet_portal' => $this->featureFlagService->isEnabled('fleet_portal', $tenantId),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'active_subscription' => $subscription,
                'plan_name' => $planName,
                'branch_limit' => $this->featureFlagService->getBranchLimit($tenantId),
                'features' => $features,
                'invoices' => $invoices,
            ]
        ]);
    }

    /**
     * Start a checkout session (returns mock checkout URL / registers pending subscription)
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan_name' => 'required|string|in:base,pro,enterprise',
            'payment_gateway' => 'required|string|in:stripe,bkash,sslcommerz',
        ]);

        $tenantId = $request->user()->tenant_id;
        $planName = $request->input('plan_name');
        $gateway = $request->input('payment_gateway');

        // Idempotency: db transaction
        try {
            $subscription = $this->subscriptionEngine->createSubscription($tenantId, $planName, $gateway);

            // Generate mock checkout url based on gateway
            $checkoutUrl = url("/checkout/verify?gateway={$gateway}&sub_id={$subscription->gateway_subscription_id}");

            return response()->json([
                'success' => true,
                'message' => 'Checkout session initialized',
                'checkout_url' => $checkoutUrl,
                'subscription' => $subscription,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize subscription checkout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the current subscription
     */
    public function cancel(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        try {
            DB::transaction(function () use ($tenantId) {
                DB::table('tenant_subscriptions')
                    ->where('tenant_id', $tenantId)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'cancelled',
                        'ends_at' => now(),
                        'updated_at' => now()
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cancellation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process sandbox simulated webhooks for deterministic testing & offline validation.
     */
    public function mockWebhook(Request $request)
    {
        $request->validate([
            'gateway' => 'required|string|in:stripe,bkash,sslcommerz',
            'payload' => 'required|array',
            'payload.subscription_id' => 'required|string',
            'payload.status' => 'required|string',
            'payload.plan_name' => 'required|string',
            'payload.tenant_id' => 'required|integer',
        ]);

        $gateway = $request->input('gateway');
        $payload = $request->input('payload');

        $processed = $this->subscriptionEngine->processWebhook($gateway, $payload);

        if ($processed) {
            return response()->json([
                'success' => true,
                'message' => 'Simulated billing webhook processed successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to process simulated billing webhook.'
        ], 400);
    }
}
