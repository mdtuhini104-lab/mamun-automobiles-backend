<?php

namespace App\Services;

use App\Repositories\DashboardRepository;
use Illuminate\Support\Facades\Cache;

class DashboardService extends BaseService
{
    protected DashboardRepository $repository;

    public function __construct(DashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getDashboardData(): array
    {
        $fetchData = function () {
            return [
                'summary' => $this->repository->getSummaryStats(),
                'monthly_sales' => $this->repository->getMonthlySales(),
                'recent_invoices' => $this->repository->getRecentInvoices(),
                'recent_job_cards' => $this->repository->getRecentJobCards(),
                'top_selling_parts' => $this->repository->getTopSellingParts(),
                'job_card_status_stats' => $this->repository->getJobCardStatusStats(),
            ];
        };

        try {
            // Bypass file cache write attempts in read-only serverless Vercel environments
            if (env('VERCEL') || env('NOW_OUTPUT_DIR') || config('cache.default') === 'file' && app()->environment('production')) {
                return $fetchData();
            }
            return Cache::remember('dashboard_data', 600, $fetchData);
        } catch (\Throwable $e) {
            // Safe fallback to run queries directly if cache driver fails
            return $fetchData();
        }
    }
}
