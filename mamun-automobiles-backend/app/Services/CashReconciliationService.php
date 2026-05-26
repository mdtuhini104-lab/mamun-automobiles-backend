<?php

namespace App\Services;

use App\Models\CashReconciliation;
use App\Models\Cashbook;
use Illuminate\Support\Facades\DB;

class CashReconciliationService
{
    /**
     * Reconcile a cashbook's expected vs actual balance.
     */
    public function reconcile($cashbookId, $actualBalance, $notes = '')
    {
        return DB::transaction(function () use ($cashbookId, $actualBalance, $notes) {
            $cashbook = Cashbook::findOrFail($cashbookId);
            
            $expectedBalance = $cashbook->current_balance;
            $difference = $actualBalance - $expectedBalance;
            
            $reconciliation = CashReconciliation::create([
                'cashbook_id' => $cashbookId,
                'expected_balance' => $expectedBalance,
                'actual_balance' => $actualBalance,
                'difference' => $difference,
                'status' => 'pending',
                'notes' => $notes,
                'verified_by' => auth()->id() ?? 1
            ]);

            return $reconciliation;
        });
    }
}
