<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RemovedQuotationItem;
use App\Models\CustomerApproval;
use App\Models\QuotationVersionSnapshot;
use App\Models\JobCard;
use App\Models\WorkflowState;
use App\Models\WorkflowHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuotationService
{
    protected $pricingEngine;

    public function __construct(CustomerPricingEngine $pricingEngine)
    {
        $this->pricingEngine = $pricingEngine;
    }

    /**
     * Create a new draft quotation for a Job Card.
     */
    public function createQuotation(int $jobCardId, array $data): Quotation
    {
        return DB::transaction(function () use ($jobCardId, $data) {
            $jobCard = JobCard::findOrFail($jobCardId);

            // Generate Quotation Number: QT-YYYY-RANDOM
            $quotationNumber = 'QT-' . date('Y') . '-' . Str::upper(Str::random(6));

            $quotation = Quotation::create([
                'job_card_id' => $jobCardId,
                'quotation_number' => $quotationNumber,
                'version' => 1,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'discount' => $data['discount'] ?? 0.00,
                'tax' => $data['tax'] ?? 0.00,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Save items
            $this->saveQuotationItems($quotation, $data['items'] ?? []);

            // Recalculate totals
            $this->recalculateQuotationTotals($quotation);

            // Trigger dynamic workflow state transition to 'quotation_draft'
            $this->transitionWorkflowState($jobCard, 'quotation_draft', 'Quotation draft created.');

            return $quotation;
        });
    }

    /**
     * Revise a quotation to a new version.
     */
    public function reviseQuotation(int $quotationId, array $data, string $reason): Quotation
    {
        if (strlen($reason) < 10 || strlen($reason) > 500) {
            throw new \Exception('Quotation revision reason must be between 10 and 500 characters.');
        }

        return DB::transaction(function () use ($quotationId, $data, $reason) {
            $quotation = Quotation::findOrFail($quotationId);

            if (in_array($quotation->status, ['approved', 'partially_approved', 'completed'])) {
                throw new \Exception('Immutable financial protection guard: Approved quotations cannot be revised.');
            }

            // 1. Create a Snapshot of the current version first
            $snapshotData = $quotation->load('items')->toArray();
            QuotationVersionSnapshot::create([
                'quotation_id' => $quotation->id,
                'version' => $quotation->version,
                'snapshot_data' => $snapshotData,
                'created_by' => auth()->id() ?? 1,
            ]);

            // 2. Identify removed items to populate removed_quotation_items
            $newItemsMap = collect($data['items'] ?? []);
            foreach ($quotation->items as $oldItem) {
                // If it's a product and no matching part_id, or service and no matching service_name in new items
                $stillExists = $newItemsMap->contains(function ($newItem) use ($oldItem) {
                    if ($oldItem->item_type === 'product') {
                        return isset($newItem['part_id']) && $newItem['part_id'] == $oldItem->part_id;
                    } else {
                        return isset($newItem['service_name']) && $newItem['service_name'] == $oldItem->service_name;
                    }
                });

                if (!$stillExists) {
                    RemovedQuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'item_name' => $oldItem->item_type === 'product' ? ($oldItem->part->name ?? 'Part') : $oldItem->service_name,
                        'removed_by_id' => auth()->id() ?? 1,
                        'removal_reason' => $reason,
                        'previous_quantity' => $oldItem->quantity,
                        'previous_price' => $oldItem->unit_price,
                    ]);
                }
            }

            // 3. Clear old items and save new items
            $quotation->items()->delete();
            $this->saveQuotationItems($quotation, $data['items'] ?? []);

            // 4. Increment version and status
            $quotation->version += 1;
            $quotation->status = 'revised';
            $quotation->discount = $data['discount'] ?? $quotation->discount;
            $quotation->tax = $data['tax'] ?? $quotation->tax;
            $quotation->notes = $data['notes'] ?? $quotation->notes;
            $quotation->save();

            // Recalculate totals
            $this->recalculateQuotationTotals($quotation);

            return $quotation;
        });
    }

    /**
     * Remove single item from quotation with auditing.
     */
    public function removeQuotationItem(int $itemId, string $reason): bool
    {
        if (strlen($reason) < 10 || strlen($reason) > 500) {
            throw new \Exception('Quotation item removal reason must be between 10 and 500 characters.');
        }

        return DB::transaction(function () use ($itemId, $reason) {
            $item = QuotationItem::findOrFail($itemId);
            $quotation = $item->quotation;

            if (in_array($quotation->status, ['approved', 'partially_approved', 'completed'])) {
                throw new \Exception('Immutable financial protection guard: Approved quotation items cannot be removed.');
            }

            // Audit log in removed_quotation_items
            RemovedQuotationItem::create([
                'quotation_id' => $quotation->id,
                'item_name' => $item->item_type === 'product' ? ($item->part->name ?? 'Part') : $item->service_name,
                'removed_by_id' => auth()->id() ?? 1,
                'removal_reason' => $reason,
                'previous_quantity' => $item->quantity,
                'previous_price' => $item->unit_price,
            ]);

            // Soft delete item
            $item->delete();

            // Recalculate totals
            $this->recalculateQuotationTotals($quotation);

            return true;
        });
    }

    /**
     * Record customer approval.
     */
    public function recordCustomerApproval(int $quotationId, array $approvalData): CustomerApproval
    {
        return DB::transaction(function () use ($quotationId, $approvalData) {
            $quotation = Quotation::findOrFail($quotationId);

            $status = $approvalData['status']; // approved, rejected, partially_approved
            $approvedItemIds = $approvalData['approved_items'] ?? [];
            $rejectedItemIds = $approvalData['rejected_items'] ?? [];

            // 1. Create the customer approval record
            $approval = CustomerApproval::create([
                'quotation_id' => $quotationId,
                'status' => $status,
                'approved_by' => $approvalData['approved_by'],
                'user_id' => auth()->id() ?? 1,
                'signature_path' => $approvalData['signature_path'] ?? null,
                'notes' => $approvalData['notes'] ?? null,
                'approved_items' => $approvedItemIds,
                'rejected_items' => $rejectedItemIds,
            ]);

            // 2. Update status of items in the quotation
            if ($status === 'approved') {
                $quotation->items()->update(['status' => 'approved']);
            } elseif ($status === 'rejected') {
                $quotation->items()->update(['status' => 'rejected']);
            } elseif ($status === 'partially_approved') {
                foreach ($quotation->items as $item) {
                    if (in_array($item->id, $approvedItemIds)) {
                        $item->update(['status' => 'approved']);
                    } else {
                        $item->update(['status' => 'rejected']);
                    }
                }
            }

            // 3. Update quotation status
            $quotation->status = $status;
            $quotation->save();

            $jobCard = $quotation->jobCard;

            if ($status === 'approved' || $status === 'partially_approved') {
                // Reserve inventory hold safely using pessimistic locks
                $reservationEngine = app(InventoryReservationEngine::class);
                $reservationEngine->reserveStock($quotation);

                // Transition workflow to 'quotation_approved'
                $this->transitionWorkflowState($jobCard, 'quotation_approved', 'Customer approved the quotation. Stock reserved.');

                // Dispatch Event
                event(new \App\Events\QuotationApproved($quotation));

                // Trigger dynamic automatic Work Order generation
                $workOrderService = app(WorkOrderService::class);
                $workOrder = $workOrderService->generateFromQuotation($quotation);

                // Transition workflow to 'work_order_active'
                $this->transitionWorkflowState($jobCard, 'work_order_active', 'Work Order generated successfully.');
            } else {
                // Transition workflow to inspected or draft due to rejection
                $this->transitionWorkflowState($jobCard, 'cancelled', 'Quotation rejected by customer.');
            }

            return $approval;
        });
    }

    // --- Helper Methods ---

    /**
     * Save items into quotation with custom pricing auto-loading.
     */
    private function saveQuotationItems(Quotation $quotation, array $items)
    {
        $customerId = $quotation->jobCard->customer_id;
        $customerName = $quotation->jobCard->customer->name ?? 'Customer';

        foreach ($items as $item) {
            $itemType = $item['item_type'];
            $partId = $item['part_id'] ?? null;
            $serviceName = $item['service_name'] ?? null;

            // Auto-load customer contract/historical pricing
            $unitPrice = $item['unit_price'] ?? null;
            if ($unitPrice === null) {
                $unitPrice = $this->pricingEngine->getRateForCustomer($customerId, $partId, $serviceName);
            }

            $laborCost = $item['labor_cost'] ?? 0.00;
            if ($itemType === 'service' && isset($item['service_name']) && !isset($item['labor_cost'])) {
                $laborCost = $this->pricingEngine->getRateForCustomer($customerId, null, $serviceName);
            }

            $partName = null;
            $sku = null;
            if ($partId) {
                $part = \App\Models\Part::find($partId);
                $partName = $part->name ?? null;
                $sku = $part->sku ?? null;
            }

            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'item_type' => $itemType,
                'part_id' => $partId,
                'part_name_snapshot' => $partName,
                'sku_snapshot' => $sku,
                'customer_name_snapshot' => $customerName,
                'service_name' => $serviceName,
                'quantity' => $item['quantity'] ?? 1.00,
                'unit_price' => $unitPrice,
                'price_snapshot' => $unitPrice,
                'discount' => $item['discount'] ?? 0.00,
                'tax' => $item['tax'] ?? 0.00,
                'tax_snapshot' => $item['tax'] ?? 0.00,
                'labor_cost' => $laborCost,
                'labor_snapshot' => $laborCost,
                'estimated_hours' => $item['estimated_hours'] ?? 0.00,
                'source_type' => $item['source_type'] ?? 'workshop_supplied',
                'status' => 'pending',
                'ai_price_recommendation' => $item['ai_price_recommendation'] ?? null,
            ]);
        }
    }

    /**
     * Recalculate and update the totals for a quotation.
     */
    public function recalculateQuotationTotals(Quotation $quotation)
    {
        $productCost = 0.00;
        $laborCost = 0.00;

        foreach ($quotation->items()->get() as $item) {
            if ($item->item_type === 'product') {
                $productCost += $item->quantity * $item->unit_price;
            } else {
                $laborCost += $item->labor_cost;
            }
        }

        $subtotal = $productCost + $laborCost - $quotation->discount;
        $grandTotal = $subtotal + $quotation->tax;

        $quotation->update([
            'total_product_cost' => $productCost,
            'total_labor_cost' => $laborCost,
            'grand_total' => $grandTotal,
        ]);
    }

    /**
     * Send quotation to customer (logs activity and triggers dispatch).
     */
    public function sendToCustomer(int $id): bool
    {
        $quotation = Quotation::findOrFail($id);
        $jobCard = $quotation->jobCard;

        // Transition workflow state to 'quotation_sent'
        $this->transitionWorkflowState($jobCard, 'quotation_sent', 'Quotation dispatched to customer.');

        // Update quotation status to sent/pending approval
        $quotation->status = 'draft'; // remains in draft but is sent
        $quotation->save();

        return true;
    }

    /**
     * Transitions dynamic workflow state and records in history.
     */
    private function transitionWorkflowState(JobCard $jobCard, string $toStateName, string $notes = '')
    {
        $fromState = $jobCard->service_status->value ?? 'pending';

        // Update JobCard service status
        $jobCard->service_status = \App\Enums\ServiceStatus::from(
            $toStateName === 'work_order_active' ? 'in_progress' : ($toStateName === 'completed' ? 'completed' : 'pending')
        );
        $jobCard->save();

        // Add to workflow history log
        WorkflowHistory::create([
            'job_card_id' => $jobCard->id,
            'from_state' => $fromState,
            'to_state' => $toStateName,
            'user_id' => auth()->id() ?? 1,
            'notes' => $notes,
        ]);
    }
}
