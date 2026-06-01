<?php

namespace App\Http\Controllers\Api\V1;

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
    protected $pdfService;

    public function __construct(InvoiceService $invoiceService, \App\Services\PdfService $pdfService)
    {
        $this->invoiceService = $invoiceService;
        $this->pdfService = $pdfService;
    }

    /**
     * Download invoice PDF.
     */
    public function downloadPdf(int $id)
    {
        $invoice = $this->invoiceService->getInvoice($id);
        
        if (!$invoice) {
            return $this->errorResponse('Invoice not found', 404);
        }
        
        $this->authorize('view', $invoice);
        
        $pdf = $this->pdfService->getInvoicePdf($invoice);
        
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Generate invoice from job card.
     */
    public function generate(int $jobCardId): JsonResponse
    {
        $this->authorize('create', \App\Models\Invoice::class);
        
        try {
            $invoice = $this->invoiceService->generateInvoiceFromJobCard($jobCardId);
            return $this->successResponse(new InvoiceResource($invoice), 'Invoice generated successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Queue generating and emailing invoice PDF.
     */
    public function emailPdf(int $id): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoice($id);
        
        if (!$invoice) {
            return $this->errorResponse('Invoice not found', 404);
        }
        
        $this->authorize('view', $invoice);
        
        try {
            \App\Jobs\GeneratePDFInvoiceJob::dispatch($invoice->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Queue dispatch failed, falling back to sync: " . $e->getMessage());
            \App\Jobs\GeneratePDFInvoiceJob::dispatchSync($invoice->id);
        }
        
        return $this->successResponse(null, 'Invoice PDF generation has been processed');
    }

    /**
     * List all invoices with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $filters = $request->only(['payment_status', 'customer_id', 'search', 'page', 'per_page', 'sort_by', 'sort_order']);
        $invoices = $this->invoiceService->listInvoices($filters);
        
        $meta = [
            'current_page' => $invoices->currentPage(),
            'per_page' => $invoices->perPage(),
            'total' => $invoices->total(),
            'last_page' => $invoices->lastPage(),
        ];
        
        return $this->successResponse(InvoiceResource::collection($invoices->items()), 'Invoices retrieved successfully', 200, $meta);
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
        
        $this->authorize('view', $invoice);
        
        return $this->successResponse(new InvoiceResource($invoice), 'Invoice details retrieved successfully');
    }

    /**
     * Process payment for an invoice.
     */
    public function pay(ProcessPaymentRequest $request, int $id): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoice($id);
        
        if (!$invoice) {
            return $this->errorResponse('Invoice not found', 404);
        }

        $this->authorize('update', $invoice);

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
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $invoices = $this->invoiceService->getCustomerDueInvoices($customerId);
        return $this->successResponse(InvoiceResource::collection($invoices), 'Customer due invoices retrieved successfully');
    }

    /**
     * Delete an invoice.
     */
    public function destroy(int $id): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoice($id);
        
        if (!$invoice) {
            return $this->errorResponse('Invoice not found', 404);
        }

        $this->authorize('delete', $invoice);

        $deleted = $this->invoiceService->deleteInvoice($id);
        
        if (!$deleted) {
            return $this->errorResponse('Delete failed', 400);
        }
        
        return $this->successResponse(null, 'Invoice deleted successfully');
    }
}
