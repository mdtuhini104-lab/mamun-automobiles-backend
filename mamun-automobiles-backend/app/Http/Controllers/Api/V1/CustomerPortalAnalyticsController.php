<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PortalAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerPortalAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(PortalAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Log a portal click, view, approval, or failure event (Public).
     */
    public function logEvent(Request $request): JsonResponse
    {
        $request->validate([
            'uuid' => 'required|string',
            'event_type' => 'required|string|in:quotation_view,item_expansion,approval_start,approval_cancel,approval_complete,payment_attempt,payment_failure,payment_success',
            'metadata' => 'array'
        ]);

        $uuid = $request->input('uuid');
        $eventType = $request->input('event_type');
        $metadata = $request->input('metadata', []);

        $log = $this->analyticsService->logEvent($uuid, $eventType, $metadata);

        return response()->json([
            'success' => true,
            'data' => $log,
            'message' => 'Portal engagement event recorded successfully.'
        ]);
    }

    /**
     * Get aggregate portal analytics summary (Access: Super Admin Only).
     */
    public function getAnalyticsSummary(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super Admin role required.'
            ], 403);
        }

        $summary = $this->analyticsService->getGlobalStats();

        return response()->json([
            'success' => true,
            'data' => $summary,
            'message' => 'Global customer portal analytics retrieved successfully.'
        ]);
    }
}
