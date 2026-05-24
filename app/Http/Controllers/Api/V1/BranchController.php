<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use App\Services\BranchAnalyticsService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    protected $branchService;
    protected $analyticsService;

    public function __construct(BranchService $branchService, BranchAnalyticsService $analyticsService)
    {
        $this->branchService = $branchService;
        $this->analyticsService = $analyticsService;
    }

    public function index()
    {
        return response()->json($this->branchService->getAllBranches());
    }

    public function analytics($id)
    {
        return response()->json($this->analyticsService->getAnalytics($id));
    }
}
