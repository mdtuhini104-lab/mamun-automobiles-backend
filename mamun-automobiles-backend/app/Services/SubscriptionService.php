<?php

namespace App\Services;

class SubscriptionService
{
    public function activatePlan($tenantId, $planId)
    {
        return [
            'status' => 'active',
            'plan' => 'Enterprise',
            'expires_at' => now()->addYear()
        ];
    }
}
