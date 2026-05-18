<?php

namespace App\Services;

use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceItemRepository;
use App\Repositories\JobCardRepository;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService extends BaseService
{
    protected $invoiceRepository;
    protected $invoiceItemRepository;
    protected $jobCardRepository;

    public function __construct(
        InvoiceRepository $invoiceRepository,
        InvoiceItemRepository $invoiceItemRepository,
        JobCardRepository $jobCardRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceItemRepository = $invoiceItemRepository;
        $this->jobCardRepository = $jobCardRepository;
    }

    /**
     * Generate invoice from a completed job card.
     */
    public function generateInvoiceFromJobCard(int $jobCardId): ?Invoice
    {
        return DB::transaction(function () use ($jobCardId) {
            $jobCard = $this->jobCardRepository->findById($jobCardId);
            
            if (!$jobCard) {
                throw new \Exception('Job Card not found');
            }
            
            // Check if invoice already exists for this job card
            $existingInvoice = Invoice::where('job_card_id', $jobCardId)->first();
            if ($existingInvoice) {
                return $existingInvoice;
            }
            
            // Calculate parts total
            $partsTotal = 0;
            $items = $jobCard->items;
            foreach ($items as $item) {
                $partsTotal += $item->quantity * $item->unit_price;
            }
            
            $serviceTotal = $jobCard->final_cost;
            $grandTotal = $partsTotal + $serviceTotal;
            
            // Create invoice
            $invoice = $this->invoiceRepository->create([
                'invoice_number' => 'INV-' . date('Y') . '-' . Str::upper(Str::random(6)),
                'customer_id' => $jobCard->customer_id,
                'job_card_id' => $jobCardId,
                'parts_total' => $partsTotal,
                'service_total' => $serviceTotal,
                'grand_total' => $grandTotal,
                'due_amount' => $grandTotal,
                'payment_status' => 'unpaid',
            ]);
            
            // Copy items to invoice items
            foreach ($items as $item) {
                $this->invoiceItemRepository->create([
                    'invoice_id' => $invoice->id,
                    'part_id' => $item->part_id,
                    'description' => $item->part->name ?? 'Part',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->quantity * $item->unit_price,
                ]);
            }
            
            return $invoice;
        });
    }

    /**
     * Process payment for an invoice.
     */
    public function processPayment(int $invoiceId, float $amount): bool
    {
        return DB::transaction(function () use ($invoiceId, $amount) {
            $invoice = $this->invoiceRepository->findById($invoiceId);
            
            if (!$invoice) {
                throw new \Exception('Invoice not found');
            }
            
            $newPaidAmount = $invoice->paid_amount + $amount;
            $newDueAmount = $invoice->grand_total - $newPaidAmount;
            
            if ($newDueAmount < 0) {
                throw new \Exception('Payment amount exceeds due amount');
            }
            
            $status = 'unpaid';
            if ($newDueAmount === 0.0) {
                $status = 'paid';
            } elseif ($newPaidAmount > 0) {
                $status = 'partial';
            }
            
            return $this->invoiceRepository->update($invoiceId, [
                'paid_amount' => $newPaidAmount,
                'due_amount' => $newDueAmount,
                'payment_status' => $status,
            ]);
        });
    }

    public function listInvoices(array $filters = []): Collection
    {
        return $this->invoiceRepository->getAll($filters);
    }

    public function getInvoice(int $id): ?Invoice
    {
        return $this->invoiceRepository->findById($id);
    }

    public function getCustomerDueInvoices(int $customerId): Collection
    {
        return $this->invoiceRepository->getDueByCustomer($customerId);
    }
}
