<?php

namespace App\Services;

class SSLCommerzService
{
    public function initiatePayment($data)
    {
        return ['status' => 'pending', 'gateway_url' => 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'];
    }

    public function verifyIPN($payload)
    {
        // IPN verification logic
        return true;
    }
}
