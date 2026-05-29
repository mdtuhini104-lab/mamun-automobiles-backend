<?php

namespace App\Services;

use App\Models\Part;
use App\Models\WorkOrderConsumption;
use Carbon\Carbon;

class InventoryPredictionEngine
{
    /**
     * Compile smart reorder and consumption warnings.
     */
    public function getInventoryIntelligence(): array
    {
        $now = Carbon::now();
        $ninetyDaysAgo = $now->copy()->subDays(90);

        // 1. Fetch all active parts
        $parts = Part::whereNull('deleted_at')->get();
        
        $fastMoving = [];
        $deadStock = [];
        $reorders = [];
        
        foreach ($parts as $part) {
            // Count total consumptions in past 90 days
            $consumptionCount = WorkOrderConsumption::where('part_id', $part->id)
                ->where('item_type', 'product')
                ->where('created_at', '>=', $ninetyDaysAgo)
                ->sum('actual_consumed_quantity') ?? 0.00;

            // Classify Fast-Moving (Consumed count > 10 in past 90 days)
            if ($consumptionCount > 10) {
                $fastMoving[] = [
                    'part_id' => $part->id,
                    'part_name' => $part->name,
                    'sku' => $part->sku,
                    'consumptions_90_days' => $consumptionCount,
                    'available_stock' => $part->stock_qty,
                ];
            }

            // Classify Dead Stock (Zero consumptions in past 90 days and stock exists)
            if ($consumptionCount == 0 && $part->stock_qty > 0) {
                $deadStock[] = [
                    'part_id' => $part->id,
                    'part_name' => $part->name,
                    'sku' => $part->sku,
                    'available_stock' => $part->stock_qty,
                    'capital_locked' => round($part->stock_qty * ($part->purchase_price ?? 0.00), 2),
                ];
            }

            // Reorder Alert: Available stock < reorder level
            $minThreshold = $part->min_stock_level ?? 5; // default 5 items
            if ($part->stock_qty < $minThreshold) {
                $reorders[] = [
                    'part_id' => $part->id,
                    'part_name' => $part->name,
                    'sku' => $part->sku,
                    'stock_qty' => $part->stock_qty,
                    'min_level' => $minThreshold,
                    'suggested_reorder_qty' => max(10, $minThreshold * 3), // suggest tripling the threshold
                ];
            }
        }

        // Generate dynamic reorder recommendations in AI queue
        foreach ($reorders as $reorder) {
            \App\Models\AiRecommendation::updateOrCreate([
                'recommendation_type' => 'inventory_reorder',
                'source_id' => $reorder['part_id'],
            ], [
                'suggestion_data' => [
                    'part_name' => $reorder['part_name'],
                    'sku' => $reorder['sku'],
                    'stock_qty' => $reorder['stock_qty'],
                    'suggested_qty' => $reorder['suggested_reorder_qty'],
                ],
                'confidence_score' => 95.00,
                'explanation' => "Stock levels for '{$reorder['part_name']}' ({$reorder['stock_qty']} units) have fallen below the configured minimum safety safety bounds of {$reorder['min_level']} units. Centralized purchase order creation is suggested.",
                'status' => 'pending'
            ]);
        }

        return [
            'fast_moving' => array_slice($fastMoving, 0, 10), // return top 10
            'dead_stock' => array_slice($deadStock, 0, 10),
            'reorders' => $reorders,
            'dead_stock_capital_sum' => round(collect($deadStock)->sum('capital_locked'), 2),
            'confidence_score' => 92.00
        ];
    }
}
