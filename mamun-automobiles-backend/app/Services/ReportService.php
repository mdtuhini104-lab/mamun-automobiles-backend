<?php

namespace App\Services;

use App\Repositories\InvoiceRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\PartRepository;
use Illuminate\Support\Facades\DB;

class ReportService extends BaseService
{
    protected $invoiceRepository;
    protected $purchaseRepository;
    protected $transactionRepository;
    protected $partRepository;

    public function __construct(
        InvoiceRepository $invoiceRepository,
        PurchaseRepository $purchaseRepository,
        TransactionRepository $transactionRepository,
        PartRepository $partRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->transactionRepository = $transactionRepository;
        $this->partRepository = $partRepository;
    }

    /**
     * Get financial report summary.
     */
    public function getFinancialReport(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();

        $totals = $this->transactionRepository->getTotals([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_income' => $totals['income'],
            'total_expense' => $totals['expense'],
            'net_profit' => $totals['income'] - $totals['expense'],
        ];
    }

    /**
     * Get sales report summary.
     */
    public function getSalesReport(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();

        $totals = $this->invoiceRepository->getTotals([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_sales' => $totals['grand_total'],
            'total_paid' => $totals['paid_amount'],
            'total_due' => $totals['due_amount'],
        ];
    }

    /**
     * Get purchase report summary.
     */
    public function getPurchaseReport(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();

        $totals = $this->purchaseRepository->getTotals([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_purchases' => $totals['total_amount'],
            'total_paid' => $totals['paid_amount'],
            'total_due' => $totals['due_amount'],
        ];
    }

    /**
     * Get stock report summary.
     */
    public function getStockReport(): array
    {
        $totalValue = \App\Models\Part::sum(DB::raw('stock_quantity * cost_price'));
        $totalSaleValue = \App\Models\Part::sum(DB::raw('stock_quantity * sale_price'));
        $totalItems = \App\Models\Part::sum('stock_quantity');

        $lowStockItems = $this->partRepository->getLowStock();

        return [
            'total_items' => (int) $totalItems,
            'total_cost_value' => (float) $totalValue,
            'total_sale_value' => (float) $totalSaleValue,
            'low_stock_count' => $lowStockItems->count(),
            'low_stock_items' => $lowStockItems,
        ];
    }
}
