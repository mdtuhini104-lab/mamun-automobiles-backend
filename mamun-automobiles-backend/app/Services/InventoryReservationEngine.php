<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Part;
use App\Models\WorkOrderConsumption;
use Illuminate\Support\Facades\DB;

class InventoryReservationEngine
{
    /**
     * Reserve inventory for approved quotation items.
     * Uses pessimistic locking to guarantee concurrency safety.
     */
    public function reserveStock(Quotation $quotation)
    {
        DB::transaction(function () use ($quotation) {
            $approvedProducts = $quotation->items()
                ->where('item_type', 'product')
                ->where('status', 'approved')
                ->where('source_type', 'workshop_supplied')
                ->get();

            foreach ($approvedProducts as $item) {
                if (!$item->part_id) continue;

                // Acquire pessimistic write lock on the part row
                $part = Part::where('id', $item->part_id)->lockForUpdate()->first();
                if (!$part) continue;

                $availableStock = $part->stock_quantity - $part->reserved_quantity;
                if ($availableStock < $item->quantity) {
                    throw new \Exception("Stock reservation failure: Insufficient available stock for part '{$part->name}'. Required: {$item->quantity}, Available: {$availableStock}");
                }

                $part->increment('reserved_quantity', $item->quantity);
            }
        });
    }

    /**
     * Release previously reserved stock back to available pool.
     */
    public function releaseStock(Quotation $quotation)
    {
        DB::transaction(function () use ($quotation) {
            $approvedProducts = $quotation->items()
                ->where('item_type', 'product')
                ->where('status', 'approved')
                ->where('source_type', 'workshop_supplied')
                ->get();

            foreach ($approvedProducts as $item) {
                if (!$item->part_id) continue;

                $part = Part::where('id', $item->part_id)->lockForUpdate()->first();
                if (!$part) continue;

                // Enforce minimum 0 guard
                $releaseQty = min($item->quantity, $part->reserved_quantity);
                $part->decrement('reserved_quantity', $releaseQty);
            }
        });
    }

    /**
     * Confirm inventory consumption during invoice or mid-repair execution.
     * Decrements both stock_quantity and reserved_quantity.
     */
    public function confirmConsumption(int $partId, float $qty)
    {
        DB::transaction(function () use ($partId, $qty) {
            $part = Part::where('id', $partId)->lockForUpdate()->first();
            if (!$part) return;

            // Decrement both reserved hold and physical stock
            $reservedDecrement = min($qty, $part->reserved_quantity);
            $part->decrement('reserved_quantity', $reservedDecrement);
            
            $part->decrement('stock_quantity', $qty);
            $part->save();
        });
    }
}
