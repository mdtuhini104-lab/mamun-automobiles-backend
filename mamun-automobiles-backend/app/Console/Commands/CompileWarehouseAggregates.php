<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Quotation;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Part;
use App\Models\WorkshopBay;
use App\Models\Invoice;

class CompileWarehouseAggregates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Mamun:CompileWarehouseAggregates {--date= : The date to compile (defaults to yesterday)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile raw automotive operations and financial transaction logs into aggregated reporting warehouse tables.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateStr = $this->option('date') ?: date('Y-m-d', strtotime('yesterday'));
        $this->info("Compiling reporting warehouse aggregates for: {$dateStr}...");

        // 1. Compile Quotation Conversion Metrics
        $totalQuotes = Quotation::whereDate('created_at', $dateStr)->count();
        $approvedQuotes = Quotation::whereDate('created_at', $dateStr)->whereIn('status', ['approved', 'partially_approved'])->count();
        $rejectedQuotes = Quotation::whereDate('created_at', $dateStr)->where('status', 'rejected')->count();
        $conversionRate = $totalQuotes > 0 ? ($approvedQuotes / $totalQuotes) * 100 : 0.00;

        DB::table('quotation_conversion_reports')->updateOrInsert(
            ['date' => $dateStr],
            [
                'total_quotations' => $totalQuotes,
                'total_approved' => $approvedQuotes,
                'total_rejected' => $rejectedQuotes,
                'conversion_rate' => $conversionRate,
                'updated_at' => now(),
            ]
        );

        // 2. Compile Technician Metrics (Estimated vs Actual Repair Duration Efficiency)
        // Let's look for completed tasks completed on this date
        $tasks = DB::table('job_card_tasks')
            ->whereDate('updated_at', $dateStr)
            ->where('status', 'completed')
            ->get();

        $techMetrics = [];
        foreach ($tasks as $task) {
            // Find who worked on this task in task assignments
            $assignment = DB::table('job_task_assignments')
                ->where('job_card_task_id', $task->id)
                ->first();

            if ($assignment && $assignment->employee_id) {
                $empId = $assignment->employee_id;
                if (!isset($techMetrics[$empId])) {
                    $techMetrics[$empId] = [
                        'completed_jobs' => 0,
                        'total_estimated' => 0,
                        'total_actual' => 0,
                    ];
                }

                $techMetrics[$empId]['completed_jobs']++;
                $techMetrics[$empId]['total_estimated'] += $task->estimated_minutes;
                $techMetrics[$empId]['total_actual'] += $task->actual_minutes;
            }
        }

        foreach ($techMetrics as $empId => $metrics) {
            $efficiency = $metrics['total_actual'] > 0 ? ($metrics['total_estimated'] / $metrics['total_actual']) * 100 : 100.00;
            
            // Calculate technician comeback metrics compiled for this day
            $comebacksCount = DB::table('comeback_jobs')
                ->where('technician_at_fault_id', $empId)
                ->whereDate('created_at', $dateStr)
                ->count();
            
            // Ratio = (comebacks / completed jobs) * 100
            $comebackRatio = $metrics['completed_jobs'] > 0 ? ($comebacksCount / $metrics['completed_jobs']) * 100 : 0.00;

            DB::table('technician_metrics')->updateOrInsert(
                ['date' => $dateStr, 'employee_id' => $empId],
                [
                    'completed_jobs' => $metrics['completed_jobs'],
                    'total_estimated_minutes' => $metrics['total_estimated'],
                    'total_actual_minutes' => $metrics['total_actual'],
                    'efficiency_percentage' => min($efficiency, 500.00), // cap at 500% to prevent outliers
                    'comebacks_count' => $comebacksCount,
                    'comeback_ratio' => min($comebackRatio, 100.00),
                    'updated_at' => now(),
                ]
            );
        }

        // 3. Compile Inventory Turnover
        $parts = Part::all();
        foreach ($parts as $part) {
            // Calculate starting and ending quantity on this day
            $consumedToday = DB::table('inventory_transactions')
                ->where('part_id', $part->id)
                ->whereDate('created_at', $dateStr)
                ->where('type', 'out')
                ->sum('quantity');

            $openingStock = $part->stock_quantity + $consumedToday; // naive approximation
            $turnoverRate = $openingStock > 0 ? ($consumedToday / $openingStock) * 100 : 0.00;

            DB::table('inventory_turnover_reports')->updateOrInsert(
                ['date' => $dateStr, 'part_id' => $part->id],
                [
                    'opening_stock' => $openingStock,
                    'closing_stock' => $part->stock_quantity,
                    'consumed_stock' => $consumedToday,
                    'turnover_rate' => $turnoverRate,
                    'updated_at' => now(),
                ]
            );
        }

        // 4. Compile Bay Utilization
        $bays = WorkshopBay::all();
        foreach ($bays as $bay) {
            // Utilization mock aggregation based on active job card times
            $occupiedMinutes = $bay->status === 'occupied' ? 480 : 0; // naive representation of 8hr shift
            $utilization = ($occupiedMinutes / 480) * 100;

            DB::table('bay_utilization_reports')->updateOrInsert(
                ['date' => $dateStr, 'workshop_bay_id' => $bay->id],
                [
                    'total_occupied_minutes' => $occupiedMinutes,
                    'utilization_percentage' => $utilization,
                    'updated_at' => now(),
                ]
            );
        }

        // 5. Compile Customer Lifetime lifetime value reports (runs historically across all invoices)
        $invoiceStats = Invoice::select('customer_id', DB::raw('count(*) as invoice_count'), DB::raw('sum(grand_total) as revenue'), DB::raw('max(created_at) as last_active'))
            ->groupBy('customer_id')
            ->get();

        foreach ($invoiceStats as $stat) {
            if (!$stat->customer_id) continue;
            
            DB::table('customer_lifetime_reports')->updateOrInsert(
                ['customer_id' => $stat->customer_id],
                [
                    'total_invoices' => $stat->invoice_count,
                    'total_revenue' => $stat->revenue,
                    'avg_invoice_value' => $stat->invoice_count > 0 ? $stat->revenue / $stat->invoice_count : 0.00,
                    'last_active_at' => $stat->last_active,
                    'updated_at' => now(),
                ]
            );
        }

        // 6. Compile KPI Monthly Aggregates
        $timestamp = strtotime($dateStr);
        $year = (int)date('Y', $timestamp);
        $month = (int)date('m', $timestamp);

        // Calculate metrics
        $monthlyRevenue = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('grand_total') ?: 0.00;

        // Get average conversion rate
        $avgConversion = DB::table('quotation_conversion_reports')
            ->whereBetween('date', [date('Y-m-01', $timestamp), date('Y-m-t', $timestamp)])
            ->avg('conversion_rate') ?: 0.00;

        // Get average bay utilization
        $avgBayUtil = DB::table('bay_utilization_reports')
            ->whereBetween('date', [date('Y-m-01', $timestamp), date('Y-m-t', $timestamp)])
            ->avg('utilization_percentage') ?: 0.00;

        // Get average technician efficiency
        $avgTechEff = DB::table('technician_metrics')
            ->whereBetween('date', [date('Y-m-01', $timestamp), date('Y-m-t', $timestamp)])
            ->avg('efficiency_percentage') ?: 0.00;

        // Get average inventory turnover rate
        $avgInvTurnover = DB::table('inventory_turnover_reports')
            ->whereBetween('date', [date('Y-m-01', $timestamp), date('Y-m-t', $timestamp)])
            ->avg('turnover_rate') ?: 0.00;

        // Get average comebacks rate
        $avgComeback = DB::table('technician_metrics')
            ->whereBetween('date', [date('Y-m-01', $timestamp), date('Y-m-t', $timestamp)])
            ->avg('comeback_ratio') ?: 0.00;

        // Naive mock retention rate calculation
        $retentionRate = 72.50; // standard default stable rate

        DB::table('kpi_monthly_aggregates')->updateOrInsert(
            ['year' => $year, 'month' => $month],
            [
                'revenue' => $monthlyRevenue,
                'quotation_conversion_ratio' => min($avgConversion, 100.00),
                'bay_utilization_ratio' => min($avgBayUtil, 100.00),
                'technician_efficiency_score' => min($avgTechEff, 500.00),
                'inventory_turnover' => min($avgInvTurnover, 100.00),
                'comeback_rate' => min($avgComeback, 100.00),
                'customer_retention_rate' => $retentionRate,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->info("SUCCESS: Aggregates compiled into reporting warehouse tables successfully!");
    }
}
