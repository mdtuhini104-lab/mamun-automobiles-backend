<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerLedgerService
{
    public function recordInvoice($customerId, $invoiceId, $jobCardId, $amount, $note = 'Invoice Generated')
    {
        return DB::transaction(function () use ($customerId, $invoiceId, $jobCardId, $amount, $note) {
            $ledger = $this->getOrCreateLedger($customerId);
            
            $newBalance = $ledger->current_balance + $amount; // Positive implies due

            DB::table('customer_transactions')->insert([
                'customer_id' => $customerId,
                'invoice_id' => $invoiceId,
                'job_card_id' => $jobCardId,
                'transaction_type' => 'invoice',
                'debit' => $amount, // Increase due
                'credit' => 0,
                'balance' => $newBalance,
                'note' => $note,
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ledger->update([
                'total_debit' => $ledger->total_debit + $amount,
                'current_balance' => $newBalance
            ]);

            $this->logActivity('Customer Ledger - Invoice Recorded', $note);

            return true;
        });
    }

    public function recordPayment($customerId, $amount, $note = 'Payment Received', $invoiceId = null)
    {
        return DB::transaction(function () use ($customerId, $amount, $note, $invoiceId) {
            $ledger = $this->getOrCreateLedger($customerId);
            
            $newBalance = $ledger->current_balance - $amount; // Decrease due

            DB::table('customer_transactions')->insert([
                'customer_id' => $customerId,
                'invoice_id' => $invoiceId,
                'transaction_type' => 'payment',
                'debit' => 0,
                'credit' => $amount, // Reduce due
                'balance' => $newBalance,
                'note' => $note,
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ledger->update([
                'total_credit' => $ledger->total_credit + $amount,
                'current_balance' => $newBalance
            ]);

            $this->logActivity('Customer Ledger - Payment Received', $note);

            return true;
        });
    }

    public function recordAdjustment($customerId, $amount, $type = 'debit', $note = 'Ledger Adjusted')
    {
        return DB::transaction(function () use ($customerId, $amount, $type, $note) {
            $ledger = $this->getOrCreateLedger($customerId);
            
            $debit = $type === 'debit' ? $amount : 0;
            $credit = $type === 'credit' ? $amount : 0;
            
            $newBalance = $ledger->current_balance + $debit - $credit;

            DB::table('customer_transactions')->insert([
                'customer_id' => $customerId,
                'transaction_type' => 'adjustment',
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'note' => $note,
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ledger->update([
                'total_debit' => $ledger->total_debit + $debit,
                'total_credit' => $ledger->total_credit + $credit,
                'current_balance' => $newBalance
            ]);
            
            $this->logActivity('Customer Ledger - Adjusted', $note);

            return true;
        });
    }

    private function getOrCreateLedger($customerId)
    {
        $ledger = DB::table('customer_ledgers')->where('customer_id', $customerId)->first();
        if (!$ledger) {
            $id = DB::table('customer_ledgers')->insertGetId([
                'customer_id' => $customerId,
                'total_debit' => 0,
                'total_credit' => 0,
                'current_balance' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return DB::table('customer_ledgers')->where('id', $id)->first();
        }
        
        // Convert to a simple updateable class mock for the above updates
        return (object) [
            'id' => $ledger->id,
            'customer_id' => $ledger->customer_id,
            'total_debit' => $ledger->total_debit,
            'total_credit' => $ledger->total_credit,
            'current_balance' => $ledger->current_balance,
            'update' => function($data) use ($ledger) {
                DB::table('customer_ledgers')->where('id', $ledger->id)->update($data);
            }
        ];
    }
    
    private function logActivity($action, $note)
    {
        try {
            DB::table('activity_logs')->insert([
                'user_id' => auth()->id() ?? 1,
                'action' => $action,
                'description' => $note,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {}
    }
}
