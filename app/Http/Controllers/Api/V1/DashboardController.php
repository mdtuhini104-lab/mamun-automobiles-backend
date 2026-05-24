<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getDashboardData();
            return $this->successResponse($data, 'Dashboard data retrieved successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DASHBOARD ERROR: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
