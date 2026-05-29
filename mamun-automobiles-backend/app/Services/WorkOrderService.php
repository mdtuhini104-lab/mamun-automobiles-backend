<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\WorkOrderConsumption;
use App\Models\JobCard;
use App\Models\JobCardItem;
use App\Models\JobCardTask;
use App\Models\Part;
use App\Models\WorkflowHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkOrderService
{
    /**
     * Generate Work Order automatically once a quotation is approved.
     */
    public function generateFromQuotation(Quotation $quotation): WorkOrder
    {
        return DB::transaction(function () use ($quotation) {
            // Check if Work Order already exists
            $existing = WorkOrder::where('quotation_id', $quotation->id)->first();
            if ($existing) {
                return $existing;
            }

            // Generate work order number: WO-YYYY-RANDOM
            $woNumber = 'WO-' . date('Y') . '-' . Str::upper(Str::random(6));

            // Extract unique departments from service items (defaulting based on service name matching)
            $departments = [];
            $services = $quotation->items()->where('item_type', 'service')->where('status', 'approved')->get();
            foreach ($services as $service) {
                $serviceName = strtolower($service->service_name);
                if (str_contains($serviceName, 'engine') || str_contains($serviceName, 'tune')) {
                    $departments[] = 'Engine';
                } elseif (str_contains($serviceName, 'electric') || str_contains($serviceName, 'wire') || str_contains($serviceName, 'battery')) {
                    $departments[] = 'Electrical';
                } elseif (str_contains($serviceName, 'ac ') || str_contains($serviceName, 'cool') || str_contains($serviceName, 'air cond')) {
                    $departments[] = 'AC';
                } elseif (str_contains($serviceName, 'dent') || str_contains($serviceName, 'body')) {
                    $departments[] = 'Denting';
                } elseif (str_contains($serviceName, 'paint')) {
                    $departments[] = 'Painting';
                } elseif (str_contains($serviceName, 'wash') || str_contains($serviceName, 'clean') || str_contains($serviceName, 'polish')) {
                    $departments[] = 'Washing';
                } else {
                    $departments[] = 'Diagnostics';
                }
            }
            $departments = array_unique($departments);

            // AI hooks mock details
            $aiHours = count($services) * 2.5; // simple prediction hook
            $aiScore = 92.5; // simple efficiency metric hook

            $workOrder = WorkOrder::create([
                'job_card_id' => $quotation->job_card_id,
                'quotation_id' => $quotation->id,
                'work_order_number' => $woNumber,
                'status' => 'pending',
                'department_allocations' => $departments,
                'ai_estimated_completion_hours' => $aiHours,
                'ai_efficiency_score' => $aiScore,
                'notes' => $quotation->notes,
            ]);

            // Automatically decompose approved service lines into repair tasks!
            foreach ($services as $service) {
                JobCardTask::create([
                    'job_card_id' => $quotation->job_card_id,
                    'name' => $service->service_name,
                    'description' => 'Decomposed from approved quotation service.',
                    'estimated_minutes' => $service->estimated_hours * 60,
                    'status' => 'pending',
                ]);

                // Create a service consumption entry
                WorkOrderConsumption::create([
                    'work_order_id' => $workOrder->id,
                    'item_type' => 'service',
                    'service_name' => $service->service_name,
                    'quantity' => 1.00,
                    'actual_consumed_quantity' => 1.00,
                    'wasted_quantity' => 0.00,
                    'returned_quantity' => 0.00,
                    'unit_price' => $service->labor_cost,
                    'source_type' => 'workshop_supplied',
                    'consumed_by_id' => auth()->id() ?? 1,
                    'is_approved' => true,
                    'approved_by_id' => auth()->id() ?? 1,
                ]);
            }

            // Sync approved products to JobCardItems and WorkOrderConsumption
            $products = $quotation->items()->where('item_type', 'product')->where('status', 'approved')->get();
            foreach ($products as $prod) {
                // Create a product consumption entry
                WorkOrderConsumption::create([
                    'work_order_id' => $workOrder->id,
                    'item_type' => 'product',
                    'part_id' => $prod->part_id,
                    'quantity' => $prod->quantity,
                    'actual_consumed_quantity' => $prod->quantity, // default to original quote estimate
                    'wasted_quantity' => 0.00,
                    'returned_quantity' => 0.00,
                    'unit_price' => $prod->unit_price,
                    'source_type' => $prod->source_type,
                    'consumed_by_id' => auth()->id() ?? 1,
                    'is_approved' => true,
                    'approved_by_id' => auth()->id() ?? 1,
                ]);

                // Check if already in JobCardItems, if not add
                $itemExists = JobCardItem::where('job_card_id', $quotation->job_card_id)
                    ->where('part_id', $prod->part_id)
                    ->exists();

                if (!$itemExists) {
                    JobCardItem::create([
                        'job_card_id' => $quotation->job_card_id,
                        'part_id' => $prod->part_id,
                        'quantity' => $prod->quantity,
                        'unit_price' => $prod->unit_price,
                    ]);
                }
            }
            // Dispatch Event
            event(new \App\Events\WorkOrderCreated($workOrder));

            return $workOrder;
        });
    }

    /**
     * Update Work Order operational status.
     */
    public function updateStatus(int $workOrderId, string $status): bool
    {
        return DB::transaction(function () use ($workOrderId, $status) {
            $workOrder = WorkOrder::findOrFail($workOrderId);
            $oldStatus = $workOrder->status;
            
            $workOrder->status = $status;
            if ($status === 'in_progress' && !$workOrder->started_at) {
                $workOrder->started_at = now();
            }
            if ($status === 'completed' && !$workOrder->completed_at) {
                $workOrder->completed_at = now();
            }
            $workOrder->save();

            // Sync Job Card Status
            $jobCard = $workOrder->jobCard;
            
            if ($status === 'completed') {
                $jobCard->service_status = \App\Enums\ServiceStatus::COMPLETED;
                $jobCard->save();
                
                // Add to workflow history
                WorkflowHistory::create([
                    'job_card_id' => $jobCard->id,
                    'from_state' => 'work_order_active',
                    'to_state' => 'completed',
                    'user_id' => auth()->id() ?? 1,
                    'notes' => 'Work order operational execution completed.',
                ]);

                // AUTOMATION: Generate final Invoice automatically on completed workflow state transition
                $invoiceService = app(\App\Services\InvoiceService::class);
                $invoiceService->generateInvoiceFromJobCard($jobCard->id);
            }

            return true;
        });
    }

    /**
     * Add additional consumed parts/services mid-repair.
     */
    public function addAdditionalItem(int $workOrderId, array $data): bool
    {
        return DB::transaction(function () use ($workOrderId, $data) {
            $workOrder = WorkOrder::findOrFail($workOrderId);
            $itemType = $data['item_type']; // product, service
            $partId = $data['part_id'] ?? null;
            $serviceName = $data['service_name'] ?? null;
            $quantity = $data['quantity'] ?? 1.00;
            $notes = $data['notes'] ?? null;

            // Fetch pricing for additional item
            $unitPrice = $data['unit_price'] ?? null;
            if ($unitPrice === null) {
                $pricingEngine = app(CustomerPricingEngine::class);
                $unitPrice = $pricingEngine->getRateForCustomer($workOrder->jobCard->customer_id, $partId, $serviceName);
            }

            // Create work_order_consumption record
            $consumption = WorkOrderConsumption::create([
                'work_order_id' => $workOrderId,
                'item_type' => $itemType,
                'part_id' => $partId,
                'service_name' => $serviceName,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'source_type' => $data['source_type'] ?? 'workshop_supplied',
                'consumed_by_id' => auth()->id() ?? 1,
                'notes' => $notes,
                'is_approved' => $data['is_approved'] ?? true, // automatically approve or configure
                'approved_by_id' => ($data['is_approved'] ?? true) ? (auth()->id() ?? 1) : null,
            ]);

            // Dispatch Event
            event(new \App\Events\AdditionalConsumptionAdded($consumption));

            // If approved, sync to active Job Card Items list
            if ($consumption->is_approved) {
                if ($itemType === 'product') {
                    // Reduce stock immediately if workshop supplied
                    if ($consumption->source_type === 'workshop_supplied' && $partId) {
                        $part = Part::findOrFail($partId);
                        if ($part->stock_quantity < $quantity) {
                            throw new \Exception("Insufficient stock for part: {$part->name}");
                        }
                        
                        $part->stock_quantity -= $quantity;
                        $part->save();
                    }

                    // Add to JobCardItem list
                    JobCardItem::create([
                        'job_card_id' => $workOrder->job_card_id,
                        'part_id' => $partId,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                    ]);
                } else {
                    // For services, add to decomposed tasks dynamically!
                    JobCardTask::create([
                        'job_card_id' => $workOrder->job_card_id,
                        'name' => $serviceName,
                        'description' => 'Additional service added mid-repair: ' . $notes,
                        'estimated_minutes' => 60, // default estimation
                        'status' => 'pending',
                    ]);
                }

                // Eagerly sync with approved quotation line items list so that billing captures it!
                $quotation = $workOrder->quotation;
                $quotationItem = \App\Models\QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_type' => $itemType,
                    'part_id' => $partId,
                    'service_name' => $serviceName,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => 0.00,
                    'tax' => 0.00,
                    'labor_cost' => $itemType === 'service' ? $unitPrice : 0.00,
                    'source_type' => $consumption->source_type,
                    'status' => 'approved', // already approved mid-job
                ]);

                // Recalculate quotation totals dynamically
                $quotationService = app(QuotationService::class);
                $quotationService->recalculateQuotationTotals($quotation);
            }

            return true;
        });
    }
}
