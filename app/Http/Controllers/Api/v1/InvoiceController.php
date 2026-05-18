<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\InvoiceResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponseTrait;

    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * List all invoices with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['payment_status', 'customer_id']);
        $invoices = $this->invoiceService->listInvoices($filters);
        return $this->successResponse(InvoiceResource::collection($invoices), 'Invoices retrieved successfully');
    }

    /**
     * Get invoice details.
     */
    public function show(int $id): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoice($id);
        
        if (!$invoice) {
            return $this->errorResponse('Invoice not found', 404);
        }
        
        return $this->successResponse(new InvoiceResource($invoice), 'Invoice details retrieved successfully');
    }

    /**
     * Process payment for an invoice.
     */
    public function pay(ProcessPaymentRequest $request, int $id): JsonResponse
    {
        try {
            $this->invoiceService->processPayment($id, $request->amount);
            return $this->successResponse(null, 'Payment processed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Get due invoices by customer.
     */
    public function customerDueInvoices(int $customerId): JsonResponse
    {
        $invoices = $this->invoiceService->getCustomerDueInvoices($customerId);
        return $this->successResponse(InvoiceResource::collection($invoices), 'Customer due invoices retrieved successfully');
    }
}
