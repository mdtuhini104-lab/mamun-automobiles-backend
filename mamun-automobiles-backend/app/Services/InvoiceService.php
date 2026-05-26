<?php

namespace App\Services;

use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceItemRepository;
use App\Repositories\JobCardRepository;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Services\AuditLogService;

class InvoiceService extends BaseService
{
    protected $invoiceRepository;
    protected $invoiceItemRepository;
    protected $jobCardRepository;
    protected $auditLogService;
    protected $customerLedgerService;
    protected $vehicleHistoryService;

    public function __construct(
        InvoiceRepository $invoiceRepository,
        InvoiceItemRepository $invoiceItemRepository,
        JobCardRepository $jobCardRepository,
        AuditLogService $auditLogService,
        \App\Services\CustomerLedgerService $customerLedgerService,
        \App\Services\VehicleHistoryService $vehicleHistoryService
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceItemRepository = $invoiceItemRepository;
        $this->jobCardRepository = $jobCardRepository;
        $this->auditLogService = $auditLogService;
        $this->customerLedgerService = $customerLedgerService;
        $this->vehicleHistoryService = $vehicleHistoryService;
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
            $discount = 0; // default
            $vatRate = 0.15; // 15% VAT
            
            $subTotal = $partsTotal + $serviceTotal - $discount;
            $vat = $subTotal * $vatRate;
            $grandTotal = $subTotal + $vat;
            
            // Create invoice
            $invoice = $this->invoiceRepository->create([
                'invoice_number' => 'INV-' . date('Y') . '-' . Str::upper(Str::random(6)),
                'customer_id' => $jobCard->customer_id,
                'job_card_id' => $jobCardId,
                'parts_total' => $partsTotal,
                'service_total' => $serviceTotal,
                'discount' => $discount,
                'vat' => $vat,
                'grand_total' => $grandTotal,
                'due_amount' => $grandTotal,
                'payment_status' => 'unpaid',
            ]);
            
            // Copy items to invoice items and reduce stock
            foreach ($items as $item) {
                $this->invoiceItemRepository->create([
                    'invoice_id' => $invoice->id,
                    'part_id' => $item->part_id,
                    'description' => $item->part->name ?? 'Part',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->quantity * $item->unit_price,
                ]);
                
                // Reduce stock via InventoryTransaction
                if ($item->part) {
                    $partService = app(\App\Services\PartService::class);
                    $partService->adjustStock(
                        $item->part_id,
                        $item->quantity,
                        'out',
                        'Invoice finalized',
                        'invoice',
                        $invoice->id
                    );
                }
            }
            
            $this->auditLogService->log('create_from_job_card', 'Invoice', $invoice->id, ['job_card_id' => $jobCardId]);
            
            // AUTOMATION: Record ledger debit entry
            $this->customerLedgerService->recordInvoice(
                $jobCard->customer_id,
                $invoice->id,
                $jobCardId,
                $grandTotal,
                'Invoice generated for Job Card #' . $jobCard->job_card_number
            );

            // AUTOMATION: Create vehicle history snapshot
            $servicesArr = [];
            foreach ($items as $item) {
                if ($item->type === 'service') {
                    $servicesArr[] = $item->part->name ?? 'Service';
                }
            }
            
            $partsArr = [];
            foreach ($items as $item) {
                if ($item->type === 'part') {
                    $partsArr[] = $item->part->name ?? 'Part';
                }
            }

            $this->vehicleHistoryService->recordHistory(
                $jobCard->vehicle_id,
                $jobCard->customer_id,
                $jobCardId,
                $invoice->id,
                [
                    'service_date' => now()->toDateString(),
                    'mileage' => $jobCard->mileage ?? 0,
                    'complaints' => $jobCard->complaints ?? 'Routine Service',
                    'services_done' => implode(', ', $servicesArr),
                    'parts_changed' => implode(', ', $partsArr),
                    'mechanic_name' => $jobCard->mechanic->name ?? 'Workshop Team',
                    'total_cost' => $grandTotal,
                ]
            );
            
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
            
            $updated = $this->invoiceRepository->update($invoiceId, [
                'paid_amount' => $newPaidAmount,
                'due_amount' => $newDueAmount,
                'payment_status' => $status,
            ]);

            if ($updated) {
                $this->auditLogService->log('process_payment', 'Invoice', $invoiceId, ['amount' => $amount]);
                
                // AUTOMATION: Record ledger credit entry
                $this->customerLedgerService->recordPayment(
                    $invoice->customer_id,
                    $amount,
                    'Payment received for Invoice #' . $invoice->invoice_number,
                    $invoice->id
                );
            }

            return $updated;
        });
    }

    public function deleteInvoice(int $id): bool
    {
        $deleted = $this->invoiceRepository->delete($id);
        if ($deleted) {
            $this->auditLogService->log('delete', 'Invoice', $id);
        }
        return $deleted;
    }

    public function listInvoices(array $filters = [])
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
