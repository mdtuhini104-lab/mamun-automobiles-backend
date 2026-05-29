<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Quotation;
use App\Models\Vehicle;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CustomerPortalController extends Controller
{
    use ApiResponseTrait;

    /**
     * Authenticate and authorize portal access using a secure tokenized UUID.
     */
    public function accessViaUuid(string $uuid): JsonResponse
    {
        // 1. Locate the Invoice by public_uuid
        $invoice = Invoice::where('public_uuid', $uuid)->first();
        if (!$invoice) {
            return $this->errorResponse('Invalid or expired secure portal link.', 404);
        }

        $customer = Customer::find($invoice->customer_id);
        if (!$customer) {
            return $this->errorResponse('Associated customer profile not found.', 404);
        }

        // Generate a secure access token for customer portal
        $token = $customer->createToken('customer-portal-access')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
            ],
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'grand_total' => $invoice->grand_total,
                'due_amount' => $invoice->due_amount,
                'payment_status' => $invoice->payment_status,
                'job_card_id' => $invoice->job_card_id,
            ]
        ], 'Access granted via tokenized URL link.');
    }

    /**
     * Track live repair progress for the customer's vehicle.
     */
    public function getRepairStatus(int $jobCardId): JsonResponse
    {
        $jobCard = JobCard::with(['vehicle', 'tasks', 'workflowHistories'])->find($jobCardId);
        
        if (!$jobCard) {
            return $this->errorResponse('Job Card record not found.', 404);
        }

        return $this->successResponse([
            'job_card_id' => $jobCard->id,
            'service_status' => $jobCard->service_status,
            'vehicle' => $jobCard->vehicle->make . ' ' . $jobCard->vehicle->model . ' (' . $jobCard->vehicle->license_plate . ')',
            'complaint' => $jobCard->complaint,
            'tasks' => $jobCard->tasks->map(function ($task) {
                return [
                    'name' => $task->task_name,
                    'status' => $task->status,
                    'is_completed' => $task->status === 'completed',
                ];
            }),
            'timeline' => $jobCard->workflowHistories->map(function ($h) {
                return [
                    'stage' => $h->stage,
                    'notes' => $h->notes,
                    'timestamp' => $h->created_at->toIso8601String(),
                ];
            })
        ], 'Live repair coordination status loaded.');
    }

    /**
     * Get quotation sheet details for portal approval.
     */
    public function getQuotation(int $quotationId): JsonResponse
    {
        $quotation = Quotation::with('items')->find($quotationId);
        if (!$quotation) {
            return $this->errorResponse('Quotation record not found.', 404);
        }

        return $this->successResponse($quotation, 'Quotation loaded successfully.');
    }

    /**
     * Approve or reject specific quotation lines.
     */
    public function approveQuotation(Request $request, int $quotationId): JsonResponse
    {
        $quotation = Quotation::findOrFail($quotationId);
        
        $request->validate([
            'status' => 'required|in:approved,rejected,partially_approved',
            'approved_items' => 'nullable|array',
            'approved_items.*' => 'integer|exists:quotation_items,id',
            'approved_by' => 'required|string|min:3',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($quotation, $request) {
                // Update Quotation Status
                $quotation->update([
                    'status' => $request->input('status'),
                ]);

                // Create customer approval audit log
                \App\Models\CustomerApproval::create([
                    'quotation_id' => $quotation->id,
                    'status' => $request->input('status'),
                    'approved_by' => $request->input('approved_by'),
                    'approved_items' => $request->input('approved_items', []),
                    'signature_path' => null, // Digital mockup sign-off
                    'notes' => $request->input('notes'),
                ]);

                // Record audit log
                ActivityLogService::log(
                    module: 'Customer Portal',
                    action: 'customer_approval',
                    description: "Quotation #{$quotation->id} status updated to {$quotation->status} by {$request->input('approved_by')} via portal.",
                    oldValues: ['status' => 'pending'],
                    newValues: ['status' => $quotation->status]
                );
            });

            return $this->successResponse(null, 'Quotation approvals submitted successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Fetch chronological vehicle lifecycle timeline history.
     */
    public function getVehicleHistory(int $vehicleId): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        
        $jobCards = JobCard::where('vehicle_id', $vehicleId)
            ->with(['invoices', 'quotations'])
            ->orderBy('created_at', 'desc')
            ->get();

        $timeline = [];
        foreach ($jobCards as $jc) {
            // Add Job Card entry
            $timeline[] = [
                'type' => 'job_card',
                'title' => 'Job Card Created',
                'description' => "Complaint: {$jc->complaint}. Diagnosis: {$jc->diagnosis}.",
                'date' => $jc->created_at->toDateString(),
                'mileage' => $jc->odometer_reading,
            ];

            // Add quotation items
            foreach ($jc->quotations as $q) {
                $timeline[] = [
                    'type' => 'quotation',
                    'title' => "Quotation Sheet ($q->quotation_number)",
                    'description' => "Total estimated cost BDT $q->grand_total. Status: $q->status.",
                    'date' => $q->created_at->toDateString(),
                    'mileage' => null,
                ];
            }

            // Add invoices
            if ($jc->invoices) {
                foreach ($jc->invoices as $inv) {
                    $timeline[] = [
                        'type' => 'invoice',
                        'title' => "Invoice Billed ($inv->invoice_number)",
                        'description' => "Grand Total BDT $inv->grand_total. Payment: $inv->payment_status.",
                        'date' => $inv->created_at->toDateString(),
                        'mileage' => null,
                    ];
                }
            }
        }

        return $this->successResponse([
            'vehicle' => $vehicle->make . ' ' . $vehicle->model . ' (' . $vehicle->license_plate . ')',
            'timeline' => $timeline
        ], 'Vehicle lifecycle chronological timeline history loaded.');
    }
}
