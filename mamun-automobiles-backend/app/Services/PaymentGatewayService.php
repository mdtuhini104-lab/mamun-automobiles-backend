<?php

namespace App\Services;

class PaymentGatewayService
{
    public function processWebhook($payload)
    {
        // Process webhook from SSLCommerz/Stripe
        return true;
    }
}
