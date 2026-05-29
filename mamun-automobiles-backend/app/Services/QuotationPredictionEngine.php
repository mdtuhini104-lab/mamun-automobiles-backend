<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\AiAnomalyLog;
use App\Models\AiRecommendation;
use App\Models\ActivityLogService;

class QuotationPredictionEngine
{
    /**
     * Scan a quotation to detect discount anomalies and register approvals.
     */
    public function scanQuotation(int $quotationId): array
    {
        $quotation = Quotation::with('items')->findOrFail($quotationId);
        $anomalies = [];
        $hasCritical = false;

        // 1. Item-Level Discount Scan (> 15% threshold)
        foreach ($quotation->items as $item) {
            $itemDiscountPercent = $item->discount_rate ?? 0.00;
            if ($itemDiscountPercent > 15.00) {
                $description = "Suspicious item discount: spare item '{$item->part_name_snapshot}' holds a {$itemDiscountPercent}% discount rate, exceeding the 15% threshold.";
                $anomalies[] = [
                    'rule' => 'item_discount_limit',
                    'severity' => 'medium',
                    'description' => $description,
                    'details' => ['item_id' => $item->id, 'discount' => $itemDiscountPercent]
                ];
                
                AiAnomalyLog::create([
                    'entity_type' => 'quotation',
                    'entity_id' => $quotationId,
                    'severity' => 'medium',
                    'rule_name' => 'item_discount_limit',
                    'description' => $description
                ]);
            }
        }

        // 2. Grand Total Discount Scan (> 20% threshold)
        $totalVal = $quotation->total_amount ?? 0.00;
        $discountVal = $quotation->discount_amount ?? 0.00;
        $grandTotalDiscountPercent = $totalVal > 0 ? ($discountVal / $totalVal) * 100 : 0.00;

        if ($grandTotalDiscountPercent > 20.00) {
            $hasCritical = true;
            $description = "Suspicious invoice discount: quotation grand total holds a " . round($grandTotalDiscountPercent, 2) . "% discount rate, exceeding the 20% high-risk threshold.";
            $anomalies[] = [
                'rule' => 'grand_discount_limit',
                'severity' => 'critical',
                'description' => $description,
                'details' => ['discount' => $grandTotalDiscountPercent]
            ];

            AiAnomalyLog::create([
                'entity_type' => 'quotation',
                'entity_id' => $quotationId,
                'severity' => 'critical',
                'rule_name' => 'grand_discount_limit',
                'description' => $description
            ]);
        }

        // 3. Repeated Abnormal Discount Patterns
        $historicalAnomalyCount = AiAnomalyLog::where('entity_type', 'quotation')
            ->where('entity_id', $quotationId)
            ->count();
        if ($historicalAnomalyCount > 2) {
            $description = "Audit Warning: customer account exhibits repeated abnormal discount request logs, requiring manual credit checks.";
            $anomalies[] = [
                'rule' => 'repeated_discount_pattern',
                'severity' => 'medium',
                'description' => $description,
                'details' => ['historical_count' => $historicalAnomalyCount]
            ];
        }

        // 4. Generate AI Centralized Inbox Recommendation for Overrides
        if (count($anomalies) > 0) {
            AiRecommendation::updateOrCreate([
                'recommendation_type' => 'pricing_anomaly',
                'source_id' => $quotationId,
            ], [
                'suggestion_data' => [
                    'original_discount' => round($grandTotalDiscountPercent, 2),
                    'anomalies' => $anomalies,
                ],
                'confidence_score' => $hasCritical ? 75.00 : 85.00,
                'explanation' => "Risk alert flagged because the quotation discount thresholds (" . ($hasCritical ? "20% grand total" : "15% item limit") . ") were breached. Human review is mandatory.",
                'status' => 'pending'
            ]);
        }

        return [
            'quotation_id' => $quotationId,
            'anomalies_count' => count($anomalies),
            'anomalies' => $anomalies,
            'requires_override' => count($anomalies) > 0,
            'can_auto_approve' => count($anomalies) === 0
        ];
    }

    /**
     * Override quotation anomalies with manager / super admin audit trail.
     */
    public function overrideAnomaly(int $quotationId, int $userId, string $notes): bool
    {
        $user = \App\Models\User::findOrFail($userId);
        
        // Validation: Verify if user holds either Manager or Super Admin role
        $isManager = $user->hasRole('Manager');
        $isSuperAdmin = $user->hasRole('Super Admin');

        if (!$isManager && !$isSuperAdmin) {
            throw new \Exception("Unauthorized: Anomaly overrides require Manager approval or Super Admin permissions.");
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($quotationId, $userId, $user, $notes, $isSuperAdmin) {
            // 1. Resolve all anomaly logs associated with this quotation
            AiAnomalyLog::where('entity_type', 'quotation')
                ->where('entity_id', $quotationId)
                ->where('is_resolved', false)
                ->update([
                    'is_resolved' => true,
                    'resolved_by_id' => $userId,
                    'override_notes' => $notes
                ]);

            // 2. Approve the inbox recommendation
            AiRecommendation::where('recommendation_type', 'pricing_anomaly')
                ->where('source_id', $quotationId)
                ->update([
                    'status' => 'approved',
                    'actioned_by_id' => $userId,
                    'actioned_at' => now()
                ]);

            // 3. Persist log inside audit trail
            $roleLabel = $isSuperAdmin ? 'Super Admin' : 'Manager';
            ActivityLogService::log(
                module: 'AI BI',
                action: 'anomaly_override',
                description: "{$roleLabel} {$user->name} overrode quotation discount warnings on Quotation #{$quotationId}.",
                oldValues: ['is_resolved' => false],
                newValues: ['is_resolved' => true, 'notes' => $notes, 'approver_id' => $userId]
            );
        });

        return true;
    }
}
