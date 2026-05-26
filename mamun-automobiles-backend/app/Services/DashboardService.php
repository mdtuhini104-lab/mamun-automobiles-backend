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
        return Cache::remember('dashboard_data', 600, function () {
            return [
                'summary' => $this->repository->getSummaryStats(),
                'monthly_sales' => $this->repository->getMonthlySales(),
                'recent_invoices' => $this->repository->getRecentInvoices(),
                'recent_job_cards' => $this->repository->getRecentJobCards(),
                'top_selling_parts' => $this->repository->getTopSellingParts(),
                'job_card_status_stats' => $this->repository->getJobCardStatusStats(),
            ];
        });
    }
}
