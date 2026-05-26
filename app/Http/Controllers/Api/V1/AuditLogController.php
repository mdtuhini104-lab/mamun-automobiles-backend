<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Http\Resources\AuditLogResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use ApiResponseTrait;

    protected AuditLogService $service;

    public function __construct(AuditLogService $service)
    {
        $this->service = $service;
    }

    /**
     * List all audit logs with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $logs = $this->service->listLogs($request->all());
        
        $meta = [
            'current_page' => $logs->currentPage(),
            'per_page' => $logs->perPage(),
            'total' => $logs->total(),
            'last_page' => $logs->lastPage(),
        ];
        
        return $this->successResponse(AuditLogResource::collection($logs->items()), 'Audit logs retrieved successfully', 200, $meta);
    }
}
