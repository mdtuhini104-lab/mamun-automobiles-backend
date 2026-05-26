<?php

namespace App\Services;

use App\Models\Cashbook;
use App\Models\DailyClosing;
use App\Models\CashbookTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class DailyClosingService
{
    /**
     * Prepare daily closing for a cashbook.
     */
    public function closeDay($cashbookId, $manualAdjustment = 0, $closingNotes = '')
    {
        return DB::transaction(function () use ($cashbookId, $manualAdjustment, $closingNotes) {
            $cashbook = Cashbook::findOrFail($cashbookId);
            
            // Prevent duplicate closing for today
            $todayClosing = DailyClosing::where('cashbook_id', $cashbookId)
                ->whereDate('created_at', now()->toDateString())
                ->first();
                
            if ($todayClosing) {
                throw new Exception("Day is already closed for this cashbook.");
            }

            // Calculate daily metrics from today's transactions
            $todayIncomes = CashbookTransaction::where('cashbook_id', $cashbookId)
                ->whereDate('created_at', now()->toDateString())
                ->where('transaction_type', 'income')
                ->sum('amount');
                
            $todayExpenses = CashbookTransaction::where('cashbook_id', $cashbookId)
                ->whereDate('created_at', now()->toDateString())
                ->where('transaction_type', 'expense')
                ->sum('amount');
                
            $systemBalance = $cashbook->current_balance;
            
            // Adjust balance with manual adjustment if any
            $closingBalance = $systemBalance + $manualAdjustment;
            $differenceAmount = $closingBalance - $systemBalance;

            $closing = DailyClosing::create([
                'branch_id' => $cashbook->branch_id,
                'cashbook_id' => $cashbookId,
                'opening_balance' => $cashbook->opening_balance,
                'total_income' => $todayIncomes,
                'total_expense' => $todayExpenses,
                'total_due_collected' => 0, // Should be calculated based on specific transaction categories
                'total_invoice_sales' => 0, // Should be calculated based on specific transaction categories
                'manual_adjustment' => $manualAdjustment,
                'closing_balance' => $closingBalance,
                'system_balance' => $systemBalance,
                'difference_amount' => $differenceAmount,
                'closing_notes' => $closingNotes,
                'closed_by' => auth()->id() ?? 1,
                'closed_at' => now(),
            ]);

            // Carry forward opening balance for the next day
            $cashbook->update([
                'opening_balance' => $closingBalance,
                'current_balance' => $closingBalance
            ]);

            return $closing;
        });
    }
}
