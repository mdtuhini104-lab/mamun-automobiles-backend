<?php

namespace App\Services;

class BranchAnalyticsService
{
    public function getAnalytics($branchId)
    {
        return [
            'revenue' => 500000,
            'expenses' => 200000,
            'profit' => 300000
        ];
    }
}
