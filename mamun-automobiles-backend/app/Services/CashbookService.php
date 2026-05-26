<?php

namespace App\Services;

use App\Models\Cashbook;
use App\Models\CashbookTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class CashbookService
{
    /**
     * Record an income transaction (e.g., from an invoice payment).
     */
    public function recordIncome($cashbookId, $amount, $referenceType, $referenceId, $category = 'general', $description = '')
    {
        return DB::transaction(function () use ($cashbookId, $amount, $referenceType, $referenceId, $category, $description) {
            $cashbook = Cashbook::findOrFail($cashbookId);
            
            $newBalance = $cashbook->current_balance + $amount;
            
            $transaction = CashbookTransaction::create([
                'cashbook_id' => $cashbookId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'transaction_type' => 'income',
                'category' => $category,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'description' => $description,
                'created_by' => auth()->id() ?? 1,
            ]);

            $cashbook->update(['current_balance' => $newBalance]);

            return $transaction;
        });
    }

    /**
     * Record an expense transaction.
     */
    public function recordExpense($cashbookId, $amount, $referenceType, $referenceId, $category = 'general', $description = '')
    {
        return DB::transaction(function () use ($cashbookId, $amount, $referenceType, $referenceId, $category, $description) {
            $cashbook = Cashbook::findOrFail($cashbookId);
            
            $newBalance = $cashbook->current_balance - $amount;
            
            $transaction = CashbookTransaction::create([
                'cashbook_id' => $cashbookId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'transaction_type' => 'expense',
                'category' => $category,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'description' => $description,
                'created_by' => auth()->id() ?? 1,
            ]);

            $cashbook->update(['current_balance' => $newBalance]);

            return $transaction;
        });
    }

    /**
     * Transfer funds between two cashbooks.
     */
    public function transfer($fromCashbookId, $toCashbookId, $amount, $description = '')
    {
        if ($fromCashbookId == $toCashbookId) {
            throw new Exception("Cannot transfer to the same cashbook.");
        }

        return DB::transaction(function () use ($fromCashbookId, $toCashbookId, $amount, $description) {
            // Deduct from source
            $this->recordExpense($fromCashbookId, $amount, 'transfer_out', $toCashbookId, 'transfer', $description);
            // Add to destination
            $this->recordIncome($toCashbookId, $amount, 'transfer_in', $fromCashbookId, 'transfer', $description);
            
            return true;
        });
    }
}
