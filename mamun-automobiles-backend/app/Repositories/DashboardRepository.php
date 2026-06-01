<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Part;
use Illuminate\Support\Facades\DB;

class DashboardRepository extends BaseRepository
{
    public function getSummaryStats(): array
    {
        $totalRevenue = Invoice::where('payment_status', 'paid')->sum('grand_total');
        $totalExpenses = \App\Models\Transaction::where('type', 'expense')->sum('amount');
        return [
            'total_customers' => Customer::count(),
            'total_vehicles' => Vehicle::count(),
            'total_invoices' => Invoice::count(),
            'total_revenue' => $totalRevenue,
            'total_due' => Invoice::sum('due_amount'),
            'low_stock_parts' => Part::whereRaw('stock_quantity <= low_stock_threshold')->count(),
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalRevenue - $totalExpenses,
        ];
    }

    public function getMonthlySales(): array
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $invoices = Invoice::selectRaw("CAST(strftime('%m', created_at) AS INTEGER) as month, SUM(grand_total) as total")
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get();
        } else {
            $invoices = Invoice::selectRaw('MONTH(created_at) as month, SUM(grand_total) as total')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get();
        }

        $result = [];
        foreach ($invoices as $invoice) {
            $result[] = [
                'month' => (int)$invoice->month,
                'total' => (float)$invoice->total,
            ];
        }

        return $result;
    }

    public function getRecentInvoices(int $limit = 5): array
    {
        return Invoice::with('customer')->latest()->limit($limit)->get()->toArray();
    }

    public function getRecentJobCards(int $limit = 5): array
    {
        return JobCard::with(['customer', 'vehicle'])->latest()->limit($limit)->get()->toArray();
    }

    public function getTopSellingParts(int $limit = 5): array
    {
        return \App\Models\InvoiceItem::select('part_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('part_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->with('part')
            ->get()
            ->toArray();
    }

    public function getJobCardStatusStats(): array
    {
        return JobCard::select('service_status', DB::raw('count(*) as count'))
            ->groupBy('service_status')
            ->get()
            ->toArray();
    }
}
