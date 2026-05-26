<?php

namespace App\Services;

class NagadService
{
    public function initialize($amount, $orderId)
    {
        return ['status' => 'success', 'checkoutUrl' => 'https://sandbox.myportwallet.com/api/v2/checkout'];
    }

    public function verify($paymentRefId)
    {
        return ['status' => 'verified'];
    }
}
