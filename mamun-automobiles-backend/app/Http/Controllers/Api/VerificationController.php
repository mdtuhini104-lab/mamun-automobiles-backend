<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class VerificationController extends Controller
{
    protected $verificationService;

    public function __construct(VerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    public function verifyInvoice(Request $request, $invoice_no)
    {
        $token = $request->query('token');

        if (!$token) {
            $this->logActivity('verification_failed', "Missing token for invoice {$invoice_no}", $request);
            return response()->json([
                'success' => false,
                'message' => 'Invalid Verification Token'
            ], 400);
        }

        $invoice = $this->verificationService->verifyInvoice($invoice_no, $token);

        if (!$invoice) {
            $this->logActivity('verification_failed', "Invalid token or tampered invoice {$invoice_no}", $request);
            return response()->json([
                'success' => false,
                'message' => 'Invoice Not Valid or Possible Tampering Detected'
            ], 404);
        }

        $this->logActivity('verification_success', "Successfully verified invoice {$invoice_no}", $request);

        return response()->json([
            'success' => true,
            'message' => 'Valid Invoice',
            'data' => [
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer ? $invoice->customer->first_name . ' ' . $invoice->customer->last_name : 'Walk-in Customer',
                'vehicle_number' => $invoice->jobCard && $invoice->jobCard->vehicle ? $invoice->jobCard->vehicle->plate_number : 'N/A',
                'grand_total' => $invoice->grand_total,
                'payment_status' => $invoice->payment_status,
                'date' => $invoice->created_at->format('Y-m-d H:i:s'),
                'qr_matched' => true
            ]
        ]);
    }

    private function logActivity($action, $description, Request $request)
    {
        ActivityLog::create([
            'user_id' => null, // public
            'module' => 'Verification',
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }
}
