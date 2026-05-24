<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $analyticsService;
    protected $dashboardService;

    public function __construct(AnalyticsService $analyticsService, DashboardAnalyticsService $dashboardService)
    {
        $this->analyticsService = $analyticsService;
        $this->dashboardService = $dashboardService;
    }

    public function summary()
    {
        return response()->json($this->dashboardService->getKpiSummary());
    }

    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $branchId = $request->input('branch_id');

        return response()->json($this->analyticsService->getSalesAnalytics($startDate, $endDate, $branchId));
    }
}
