<?php

namespace App\Services;

class FeatureFlagService
{
    protected $subscriptionEngine;

    public function __construct(SubscriptionEngineService $subscriptionEngine)
    {
        $this->subscriptionEngine = $subscriptionEngine;
    }

    /**
     * Determine if the active tenant has access to a specific feature flag.
     */
    public function isEnabled(string $feature, ?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? (auth()->check() ? auth()->user()->tenant_id : 1);
        return $this->subscriptionEngine->checkFeatureAccess($tenantId, $feature);
    }

    /**
     * Checks if a tenant has reached their maximum branch limit.
     */
    public function getBranchLimit(?int $tenantId = null): int
    {
        $tenantId = $tenantId ?? (auth()->check() ? auth()->user()->tenant_id : 1);
        $sub = $this->subscriptionEngine->getSubscription($tenantId);
        
        if (!$sub) {
            return 1; // Default base limit
        }

        $plan = strtolower($sub->plan_name);
        if ($plan === 'enterprise') {
            return 999; // Unlimited
        }
        if ($plan === 'pro') {
            return 3;
        }

        return 1; // Base plan
    }
}
