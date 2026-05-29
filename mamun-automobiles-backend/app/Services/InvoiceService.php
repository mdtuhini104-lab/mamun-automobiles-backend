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
            
            // 1. Check if there is an active Work Order
            $workOrder = \App\Models\WorkOrder::where('job_card_id', $jobCardId)->first();
            
            // 2. Check if there is an approved or partially approved quotation
            $quotation = \App\Models\Quotation::where('job_card_id', $jobCardId)
                ->whereIn('status', ['approved', 'partially_approved'])
                ->orderBy('version', 'desc')
                ->first();

            $partsTotal = 0;
            $serviceTotal = 0;
            $invoiceItems = [];

            if ($workOrder) {
                // Invoices MUST use actual consumption!
                $consumptions = $workOrder->consumptions()->where('is_approved', true)->get();
                
                foreach ($consumptions as $consumption) {
                    $billedPrice = $consumption->unit_price;
                    if ($consumption->item_type === 'product' && $consumption->source_type === 'customer_supplied') {
                        $billedPrice = 0.00; // Customer supplied items are NOT billed!
                    }

                    $qty = $consumption->actual_consumed_quantity > 0 ? $consumption->actual_consumed_quantity : $consumption->quantity;

                    if ($consumption->item_type === 'product') {
                        $partsTotal += $qty * $billedPrice;
                        $invoiceItems[] = [
                            'part_id' => $consumption->part_id,
                            'description' => $consumption->part->name ?? 'Part (Workshop)',
                            'quantity' => $qty,
                            'unit_price' => $billedPrice,
                            'source_type' => $consumption->source_type,
                        ];

                        // Deduct physical stock on finalization using pessimistic locks
                        if ($consumption->source_type === 'workshop_supplied' && $consumption->part_id) {
                            $reservationEngine = app(\App\Services\InventoryReservationEngine::class);
                            $reservationEngine->confirmConsumption($consumption->part_id, $qty);
                        }
                    } else {
                        $serviceTotal += $billedPrice;
                        $invoiceItems[] = [
                            'part_id' => null,
                            'description' => $consumption->service_name ?? 'Service Charge',
                            'quantity' => 1,
                            'unit_price' => $billedPrice,
                            'source_type' => 'workshop_supplied',
                        ];
                    }
                }
            } elseif ($quotation) {
                // Use quotation approved items
                $quotationItems = $quotation->items()->where('status', 'approved')->get();
                
                foreach ($quotationItems as $qItem) {
                    $billedPrice = $qItem->unit_price;
                    if ($qItem->item_type === 'product' && $qItem->source_type === 'customer_supplied') {
                        $billedPrice = 0.00; // Customer supplied items are NOT billed!
                    }

                    if ($qItem->item_type === 'product') {
                        $partsTotal += $qItem->quantity * $billedPrice;
                        $invoiceItems[] = [
                            'part_id' => $qItem->part_id,
                            'description' => $qItem->part->name ?? 'Part (Workshop)',
                            'quantity' => $qItem->quantity,
                            'unit_price' => $billedPrice,
                            'source_type' => $qItem->source_type,
                        ];
                    } else {
                        $serviceTotal += $qItem->labor_cost;
                        $invoiceItems[] = [
                            'part_id' => null,
                            'description' => $qItem->service_name ?? 'Service Charge',
                            'quantity' => 1,
                            'unit_price' => $qItem->labor_cost,
                            'source_type' => 'workshop_supplied',
                        ];
                    }
                }
            } else {
                // Fallback to legacy job card items
                $items = $jobCard->items;
                foreach ($items as $item) {
                    $partsTotal += $item->quantity * $item->unit_price;
                    $invoiceItems[] = [
                        'part_id' => $item->part_id,
                        'description' => $item->part->name ?? 'Part',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'source_type' => 'workshop_supplied',
                    ];
                }
                $serviceTotal = $jobCard->final_cost;
            }

            $discount = $quotation ? $quotation->discount : 0.00;
            $vatRate = 0.15; // 15% VAT
            
            $subTotal = $partsTotal + $serviceTotal - $discount;
            if ($subTotal < 0) $subTotal = 0.00;
            
            $vat = $subTotal * $vatRate;
            $grandTotal = $subTotal + $vat;
            
            // 2. Adjust Advance Payments from Customer Ledger
            $ledger = DB::table('customer_ledgers')->where('customer_id', $jobCard->customer_id)->first();
            $advanceAdjusted = 0.00;
            $dueAmount = $grandTotal;
            $paymentStatus = 'unpaid';

            if ($ledger && $ledger->current_balance < 0) {
                // Customer has pre-paid deposit (negative balance in ledger implies credit surplus)
                $advanceAvailable = abs($ledger->current_balance);
                $advanceAdjusted = min($grandTotal, $advanceAvailable);
                $dueAmount = $grandTotal - $advanceAdjusted;

                if ($dueAmount == 0) {
                    $paymentStatus = 'paid';
                } else {
                    $paymentStatus = 'partial';
                }
            }

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
                'paid_amount' => $advanceAdjusted,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
            ]);
            
            // Create invoice items
            foreach ($invoiceItems as $item) {
                $this->invoiceItemRepository->create([
                    'invoice_id' => $invoice->id,
                    'part_id' => $item['part_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
                
                // Reduce stock via InventoryTransaction if it's workshop supplied
                if ($item['part_id'] && $item['source_type'] === 'workshop_supplied') {
                    $partService = app(\App\Services\PartService::class);
                    $partService->adjustStock(
                        $item['part_id'],
                        $item['quantity'],
                        'out',
                        'Invoice finalized',
                        'invoice',
                        $invoice->id
                    );
                }
            }
            
            $this->auditLogService->log('create_from_job_card', 'Invoice', $invoice->id, ['job_card_id' => $jobCardId, 'advance_adjusted' => $advanceAdjusted]);
            
            // Double-Entry Journal Entry
            try {
                $accountingService = app(\App\Services\AccountingEngineService::class);
                $accountingItems = [
                    ['account_code' => '1100', 'debit' => $grandTotal, 'credit' => 0.00],
                ];
                if ($discount > 0) {
                    $accountingItems[] = ['account_code' => '3000', 'debit' => $discount, 'credit' => 0.00];
                }
                if ($partsTotal > 0) {
                    $accountingItems[] = ['account_code' => '4100', 'debit' => 0.00, 'credit' => $partsTotal];
                }
                if ($serviceTotal > 0) {
                    $accountingItems[] = ['account_code' => '4000', 'debit' => 0.00, 'credit' => $serviceTotal];
                }
                if ($vat > 0) {
                    $accountingItems[] = ['account_code' => '2200', 'debit' => 0.00, 'credit' => $vat];
                }

                $accountingService->recordJournalEntry([
                    'tenant_id' => $jobCard->tenant_id ?? 1,
                    'branch_id' => $invoice->branch_id,
                    'entry_date' => now()->format('Y-m-d'),
                    'reference_no' => $invoice->invoice_number,
                    'description' => 'Invoice finalized for Job Card #' . $jobCard->id,
                    'items' => $accountingItems
                ]);
            } catch (\Exception $e) {
                // Log and continue or halt based on strictness rules
                \Illuminate\Support\Facades\Log::error("Accounting double-entry failure: " . $e->getMessage());
            }
            
            // AUTOMATION: Record ledger debit entry
            $this->customerLedgerService->recordInvoice(
                $jobCard->customer_id,
                $invoice->id,
                $jobCardId,
                $grandTotal,
                'Invoice generated for Job Card #' . $jobCard->id
            );

            // AUTOMATION: If advance deposit was adjusted, log a corresponding offset transaction in customer_transactions
            if ($advanceAdjusted > 0) {
                $ledger = DB::table('customer_ledgers')->where('customer_id', $jobCard->customer_id)->first();
                $newLedgerBalance = $ledger->current_balance - $advanceAdjusted;

                DB::table('customer_transactions')->insert([
                    'customer_id' => $jobCard->customer_id,
                    'invoice_id' => $invoice->id,
                    'job_card_id' => $jobCardId,
                    'transaction_type' => 'payment',
                    'debit' => 0,
                    'credit' => $advanceAdjusted,
                    'balance' => $newLedgerBalance,
                    'note' => 'Advance deposit of ' . $advanceAdjusted . ' adjusted against Invoice #' . $invoice->invoice_number,
                    'created_by' => auth()->id() ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('customer_ledgers')->where('customer_id', $jobCard->customer_id)->update([
                    'total_credit' => $ledger->total_credit + $advanceAdjusted,
                    'current_balance' => $newLedgerBalance,
                    'updated_at' => now(),
                ]);
            }

            // AUTOMATION: Create vehicle history snapshot
            $servicesArr = [];
            if ($quotation) {
                $servicesArr = $quotation->items()->where('item_type', 'service')->pluck('service_name')->toArray();
            }
            
            $partsArr = [];
            foreach ($invoiceItems as $item) {
                if ($item['part_id']) {
                    $partsArr[] = $item['description'] . ($item['source_type'] === 'customer_supplied' ? ' (Customer)' : '');
                }
            }

            $this->vehicleHistoryService->recordHistory(
                $jobCard->vehicle_id,
                $jobCard->customer_id,
                $jobCardId,
                $invoice->id,
                [
                    'service_date' => now()->toDateString(),
                    'mileage' => $jobCard->odometer_reading ?? 0,
                    'complaints' => $jobCard->complaint ?? 'Routine Service',
                    'services_done' => implode(', ', $servicesArr),
                    'parts_changed' => implode(', ', $partsArr),
                    'mechanic_name' => $jobCard->mechanic->name ?? 'Workshop Team',
                    'total_cost' => $grandTotal,
                ]
            );

            // Create Service Warranty (30-day default expiry)
            \App\Models\Warranty::create([
                'job_card_id' => $jobCardId,
                'invoice_id' => $invoice->id,
                'warranty_expiry_date' => now()->addDays(30)->toDateString(),
                'status' => 'active',
            ]);

            // Dispatch Event
            event(new \App\Events\InvoiceGenerated($invoice));
            
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
                
                // Double-Entry Journal Entry
                try {
                    $accountingService = app(\App\Services\AccountingEngineService::class);
                    $accountingService->recordJournalEntry([
                        'tenant_id' => $invoice->tenant_id ?? 1,
                        'branch_id' => $invoice->branch_id,
                        'entry_date' => now()->format('Y-m-d'),
                        'reference_no' => $invoice->invoice_number,
                        'description' => 'Payment received for Invoice #' . $invoice->invoice_number,
                        'items' => [
                            ['account_code' => '1000', 'debit' => $amount, 'credit' => 0.00], // Cash
                            ['account_code' => '1100', 'debit' => 0.00, 'credit' => $amount], // Accounts Receivable
                        ]
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Accounting payment double-entry failure: " . $e->getMessage());
                }
                
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
