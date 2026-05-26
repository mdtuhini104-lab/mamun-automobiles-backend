<?php

namespace App\Services;

class DashboardAnalyticsService
{
    public function getKpiSummary()
    {
        // Fetch real-time KPI
        return [
            'stats' => [
                'daily_revenue' => 25000,
                'monthly_revenue' => 1250000,
                'total_expenses' => 450000,
                'net_profit' => 800000,
                'low_stock_alerts' => 5,
                'pending_invoices' => 12
            ],
            'trends' => [
                'categories' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'revenue' => [15000, 20000, 18000, 22000, 25000, 10000, 15000],
                'expense' => [10000, 12000, 11000, 15000, 16000, 8000, 10000]
            ],
            'mechanics' => [
                'labels' => ['John', 'Mike', 'Sarah', 'David', 'Alex'],
                'series' => [44, 55, 41, 17, 15]
            ],
            'low_stock' => [
                ['name' => 'Engine Oil 5W-30', 'current_stock' => 5, 'min_stock_level' => 10],
                ['name' => 'Brake Pads', 'current_stock' => 2, 'min_stock_level' => 8]
            ],
            'insights' => [
                ['type' => 'success', 'message' => 'Revenue increased by 15% this week.'],
                ['type' => 'warning', 'message' => 'Brake pads are running low.']
            ],
            'categories' => [],
            'series' => [],
            'charts' => []
        ];
    }
}
