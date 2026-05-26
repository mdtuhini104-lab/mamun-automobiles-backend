<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class VerificationService
{
    /**
     * Generate unique verification hash and UUID for an invoice
     */
    public function generateForInvoice(Invoice $invoice)
    {
        if (!$invoice->public_uuid) {
            $invoice->public_uuid = (string) Str::uuid();
            $invoice->verification_hash = hash('sha256', $invoice->invoice_number . config('app.key') . time());
            $invoice->qr_generated_at = now();
            $invoice->save();
        }

        return $this->getVerificationUrl($invoice);
    }

    /**
     * Get the verification URL
     */
    public function getVerificationUrl(Invoice $invoice)
    {
        $token = Crypt::encryptString($invoice->verification_hash);
        return route('verify.invoice', [
            'invoice_no' => $invoice->invoice_number,
            'token' => $token
        ]);
    }

    /**
     * Verify an invoice
     */
    public function verifyInvoice($invoice_no, $token)
    {
        try {
            $hash = Crypt::decryptString($token);
            $invoice = Invoice::where('invoice_number', $invoice_no)
                ->where('verification_hash', $hash)
                ->first();

            return $invoice;
        } catch (\Exception $e) {
            return null;
        }
    }
}
