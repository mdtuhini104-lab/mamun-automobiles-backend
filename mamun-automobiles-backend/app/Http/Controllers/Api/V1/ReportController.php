<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponseTrait;

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get financial report.
     */
    public function financialReport(Request $request): JsonResponse
    {
        $report = $this->reportService->getFinancialReport($request->all());
        return $this->successResponse($report, 'Financial report retrieved successfully');
    }

    /**
     * Get sales report.
     */
    public function salesReport(Request $request): JsonResponse
    {
        $report = $this->reportService->getSalesReport($request->all());
        return $this->successResponse($report, 'Sales report retrieved successfully');
    }

    /**
     * Get purchase report.
     */
    public function purchaseReport(Request $request): JsonResponse
    {
        $report = $this->reportService->getPurchaseReport($request->all());
        return $this->successResponse($report, 'Purchase report retrieved successfully');
    }

    /**
     * Get stock report.
     */
    public function stockReport(): JsonResponse
    {
        $report = $this->reportService->getStockReport();
        return $this->successResponse($report, 'Stock report retrieved successfully');
    }
}
