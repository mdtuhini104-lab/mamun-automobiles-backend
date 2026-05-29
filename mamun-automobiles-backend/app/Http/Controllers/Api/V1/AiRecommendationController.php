<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;
use App\Models\AiAnomalyLog;
use App\Services\QuotationPredictionEngine;
use App\Traits\ApiResponseTrait;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiRecommendationController extends Controller
{
    use ApiResponseTrait;

    protected $anomalyEngine;

    public function __construct(QuotationPredictionEngine $anomalyEngine)
    {
        $this->anomalyEngine = $anomalyEngine;
    }

    /**
     * List all active AI recommendations.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AiRecommendation::with('actionedBy');

        if ($request->has('type')) {
            $query->where('recommendation_type', $request->input('type'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', 'pending');
        }

        $recommendations = $query->orderBy('created_at', 'desc')->paginate(15);
        return $this->successResponse($recommendations->items(), 'AI recommendations retrieved successfully', 200, [
            'current_page' => $recommendations->currentPage(),
            'per_page' => $recommendations->perPage(),
            'total' => $recommendations->total(),
            'last_page' => $recommendations->lastPage(),
        ]);
    }

    /**
     * Approve an AI recommendation (Human-in-the-Loop approval).
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $rec = AiRecommendation::findOrFail($id);

            if ($rec->status !== 'pending') {
                return $this->errorResponse('Recommendation has already been actioned.', 400);
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($rec) {
                $rec->update([
                    'status' => 'approved',
                    'actioned_by_id' => auth()->id() ?? 1,
                    'actioned_at' => now(),
                ]);

                // Side effects based on recommendation type
                if ($rec->recommendation_type === 'inventory_reorder') {
                    // Automatically trigger an internal inventory low-stock flag or draft purchase
                    $part = \App\Models\Part::find($rec->source_id);
                    if ($part) {
                        $part->update(['min_stock_level' => $rec->suggestion_data['suggested_qty'] ?? $part->min_stock_level]);
                    }
                }

                // Log human confirmation in audit log
                ActivityLogService::log(
                    module: 'AI BI',
                    action: 'recommendation_approve',
                    description: "AI Recommendation #{$rec->id} ({$rec->recommendation_type}) was approved by User #" . (auth()->id() ?? 1),
                    oldValues: ['status' => 'pending'],
                    newValues: ['status' => 'approved', 'actioned_by' => auth()->id() ?? 1]
                );
            });

            return $this->successResponse($rec, 'AI recommendation approved successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Reject an AI recommendation.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $rec = AiRecommendation::findOrFail($id);

            if ($rec->status !== 'pending') {
                return $this->errorResponse('Recommendation has already been actioned.', 400);
            }

            $rec->update([
                'status' => 'rejected',
                'actioned_by_id' => auth()->id() ?? 1,
                'actioned_at' => now(),
            ]);

            ActivityLogService::log(
                module: 'AI BI',
                action: 'recommendation_reject',
                description: "AI Recommendation #{$rec->id} ({$rec->recommendation_type}) was rejected by User #" . (auth()->id() ?? 1),
                oldValues: ['status' => 'pending'],
                newValues: ['status' => 'rejected', 'actioned_by' => auth()->id() ?? 1]
            );

            return $this->successResponse($rec, 'AI recommendation rejected successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Override a quotation pricing discount anomaly.
     */
    public function overrideAnomaly(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'override_notes' => 'required|string|min:5|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            $userId = auth()->id() ?? 1;
            $this->anomalyEngine->overrideAnomaly($id, $userId, $request->input('override_notes'));
            
            return $this->successResponse(null, 'Quotation anomaly successfully overrode. Action logged in audit trails.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
