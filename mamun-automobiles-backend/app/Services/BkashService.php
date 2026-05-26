<?php

namespace App\Services;

class BkashService
{
    public function createPayment($amount, $invoiceId)
    {
        return ['status' => 'initiated', 'paymentID' => uniqid('BK_')];
    }

    public function executePayment($paymentID)
    {
        // Validation logic
        return ['status' => 'completed', 'trxID' => uniqid('TRX_')];
    }
}
