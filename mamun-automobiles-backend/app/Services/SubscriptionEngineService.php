<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class SubscriptionEngineService
{
    /**
     * Get active tenant subscription.
     */
    public function getSubscription(int $tenantId): ?object
    {
        return DB::table('tenant_subscriptions')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>=', now())
                      ->orWhere('grace_period_until', '>=', now());
            })
            ->first();
    }

    /**
     * Create or update a subscription plan.
     */
    public function createSubscription(int $tenantId, string $planName, string $gateway, ?string $subId = null): object
    {
        return DB::transaction(function () use ($tenantId, $planName, $gateway, $subId) {
            DB::table('tenant_subscriptions')->where('tenant_id', $tenantId)->update([
                'status' => 'cancelled',
                'ends_at' => now(),
                'updated_at' => now()
            ]);

            $id = DB::table('tenant_subscriptions')->insertGetId([
                'tenant_id' => $tenantId,
                'plan_name' => $planName,
                'payment_gateway' => $gateway,
                'gateway_subscription_id' => $subId ?: 'sub_' . str_random(12),
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'grace_period_until' => now()->addMonth()->addDays(5), // 5 days grace period
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return DB::table('tenant_subscriptions')->where('id', $id)->first();
        });
    }

    /**
     * Webhook Processor (Provider Abstraction Layer).
     * Handles webhook payment payloads dynamically for Stripe, bKash, Nagad, etc.
     */
    public function processWebhook(string $gateway, array $payload): bool
    {
        // Replay safety: check event ID to prevent duplicate processing
        $eventId = $payload['event_id'] ?? $payload['id'] ?? null;
        if ($eventId) {
            $cacheKey = 'processed_billing_event_' . $eventId;
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                return true; // Already processed
            }
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addDays(7));
        }

        return DB::transaction(function () use ($gateway, $payload) {
            $subId = $payload['subscription_id'] ?? null;
            if (!$subId) {
                return false;
            }

            $status = $payload['status'] ?? 'active';
            $planName = $payload['plan_name'] ?? 'pro';
            $tenantId = $payload['tenant_id'] ?? 1;

            if ($status === 'deleted' || $status === 'cancelled') {
                DB::table('tenant_subscriptions')
                    ->where('gateway_subscription_id', $subId)
                    ->update([
                        'status' => 'cancelled',
                        'ends_at' => now(),
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('tenant_subscriptions')
                    ->updateOrInsert(
                        ['gateway_subscription_id' => $subId],
                        [
                            'tenant_id' => $tenantId,
                            'plan_name' => $planName,
                            'payment_gateway' => $gateway,
                            'status' => 'active',
                            'starts_at' => now(),
                            'ends_at' => now()->addMonth(),
                            'grace_period_until' => now()->addMonth()->addDays(5),
                            'updated_at' => now(),
                            'created_at' => now()
                        ]
                    );
            }

            return true;
        });
    }

    /**
     * Check if a tenant plan has permission for a specific feature.
     */
    public function checkFeatureAccess(int $tenantId, string $feature): bool
    {
        $sub = $this->getSubscription($tenantId);
        if (!$sub) {
            // Default baseline fallback plan: 'base'
            $subPlan = 'base';
        } else {
            $subPlan = strtolower($sub->plan_name);
        }

        $features = [
            'base' => ['view_dashboard', 'manage_invoices', 'single_branch'],
            'pro' => ['view_dashboard', 'manage_invoices', 'ai_quota_assistant', 'multi_branch_3', 'audit_logs'],
            'enterprise' => ['view_dashboard', 'manage_invoices', 'ai_quota_assistant', 'ai_predictive_maintenance', 'unlimited_branches', 'audit_logs', 'white_label', 'fleet_portal']
        ];

        $allowedFeatures = $features[$subPlan] ?? $features['base'];

        return in_array($feature, $allowedFeatures);
    }
}
