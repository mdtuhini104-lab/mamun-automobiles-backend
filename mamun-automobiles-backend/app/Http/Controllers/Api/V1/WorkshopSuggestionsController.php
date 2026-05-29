<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WorkflowSuggestionsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkshopSuggestionsController extends Controller
{
    protected $suggestionsService;

    public function __construct(WorkflowSuggestionsService $suggestionsService)
    {
        $this->suggestionsService = $suggestionsService;
    }

    /**
     * Get auto-suggestions for mechanic assignment, bays, complaints, and parts.
     */
    public function getSuggestions(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $vehicleId = $request->query('vehicle_id') ? (int) $request->query('vehicle_id') : null;

        $suggestions = $this->suggestionsService->getSuggestions($tenantId, $vehicleId);

        return response()->json([
            'success' => true,
            'data' => $suggestions,
            'message' => 'Workflow auto-suggestions retrieved successfully.'
        ]);
    }
}
