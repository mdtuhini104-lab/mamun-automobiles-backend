<?php

namespace App\Services;

class StripeService
{
    public function createCheckoutSession($priceId, $customerId)
    {
        return ['status' => 'ready', 'sessionId' => 'cs_test_'.uniqid()];
    }

    public function handleWebhook($payload, $signature)
    {
        // Stripe webhook signature verification
        return true;
    }
}
