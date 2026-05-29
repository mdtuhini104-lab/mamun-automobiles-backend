<?php

namespace App\Services;

use App\Models\Part;
use App\Models\JobCardItem;
use App\Models\QuotationItem;
use App\Models\Customer;

class PricingRecommendationService
{
    /**
     * Compute recommended labor cost and markup for a spare part.
     */
    public function getRecommendations(int $partId, ?int $customerId = null): array
    {
        $part = Part::findOrFail($partId);
        
        // 1. Calculate historical average sale price from previous invoices / job card items
        $avgPrice = JobCardItem::where('part_id', $partId)
            ->average('unit_price') ?? $part->sale_price;

        // 2. Recommend optimal markup based on original purchase price
        $purchasePrice = $part->purchase_price ?? 0.00;
        $recommendedMarkup = 0.15; // default 15% markup

        if ($purchasePrice > 0) {
            $recommendedMarkup = ($avgPrice - $purchasePrice) / $purchasePrice;
            // Cap markup recommendations between 10% and 50% for safety bounds
            $recommendedMarkup = max(0.10, min(0.50, $recommendedMarkup));
        }

        $recommendedPrice = $purchasePrice * (1 + $recommendedMarkup);

        // 3. Customer pricing profile adjustments (VIP/Corporate)
        $explanation = "Standard retail markup of " . round($recommendedMarkup * 100) . "% applied over spare purchase cost.";
        $confidence = 90.00;

        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer && $customer->pricing_tier === 'corporate') {
                $recommendedPrice *= 0.90; // 10% volume discount
                $explanation = "Corporate rate applied: 10% volume contract discount active on standard " . round($recommendedMarkup * 100) . "% retail markup.";
                $confidence = 95.00;
            } elseif ($customer && $customer->pricing_tier === 'vip') {
                $recommendedPrice *= 0.95; // 5% VIP discount
                $explanation = "VIP relationship rate applied: 5% loyalty discount active on standard " . round($recommendedMarkup * 100) . "% retail markup.";
                $confidence = 92.00;
            }
        }

        return [
            'part_id' => $partId,
            'part_name' => $part->name,
            'sku' => $part->sku,
            'purchase_price' => round($purchasePrice, 2),
            'recommended_markup' => round($recommendedMarkup * 100, 2),
            'recommended_price' => round($recommendedPrice, 2),
            'explanation' => $explanation,
            'confidence_score' => $confidence
        ];
    }
}
