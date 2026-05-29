<?php

namespace App\Services;

use App\Models\AccountChart;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Support\Facades\DB;
use Exception;

class AccountingEngineService
{
    /**
     * Seeds the standard enterprise Chart of Accounts for a tenant if empty.
     */
    public function seedChartOfAccounts(int $tenantId): void
    {
        $existing = AccountChart::where('tenant_id', $tenantId)->count();
        if ($existing > 0) {
            return;
        }

        $accounts = [
            // ASSETS
            ['code' => '1000', 'name' => 'Cash', 'type' => 'asset'],
            ['code' => '1010', 'name' => 'Bank Accounts', 'type' => 'asset'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '1200', 'name' => 'Inventory Assets', 'type' => 'asset'],
            ['code' => '1300', 'name' => 'Equipment Assets', 'type' => 'asset'],
            // LIABILITIES
            ['code' => '2000', 'name' => 'Supplier Payables', 'type' => 'liability'],
            ['code' => '2100', 'name' => 'Payroll Liabilities', 'type' => 'liability'],
            ['code' => '2200', 'name' => 'Tax Payables', 'type' => 'liability'],
            // EQUITY
            ['code' => '3000', 'name' => 'Owner Capital', 'type' => 'equity'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity'],
            // REVENUE
            ['code' => '4000', 'name' => 'Workshop Service Revenue', 'type' => 'revenue'],
            ['code' => '4100', 'name' => 'Parts Sales Revenue', 'type' => 'revenue'],
            ['code' => '4200', 'name' => 'Fleet Service Revenue', 'type' => 'revenue'],
            // EXPENSES
            ['code' => '5000', 'name' => 'Salary Expense', 'type' => 'expense'],
            ['code' => '5100', 'name' => 'Utility Expense', 'type' => 'expense'],
            ['code' => '5200', 'name' => 'Inventory Loss', 'type' => 'expense'],
            ['code' => '5300', 'name' => 'Maintenance Expense', 'type' => 'expense'],
            ['code' => '5400', 'name' => 'Marketing Expense', 'type' => 'expense'],
        ];

        foreach ($accounts as $acc) {
            AccountChart::create([
                'tenant_id' => $tenantId,
                'account_code' => $acc['code'],
                'account_name' => $acc['name'],
                'account_type' => $acc['type'],
                'is_active' => true
            ]);
        }
    }

    /**
     * Records a balanced double-entry general journal entry.
     */
    public function recordJournalEntry(array $data): JournalEntry
    {
        $tenantId = $data['tenant_id'] ?? (auth()->check() ? auth()->user()->tenant_id : 1);
        $branchId = $data['branch_id'] ?? (auth()->check() ? auth()->user()->branch_id : null);
        
        $this->seedChartOfAccounts($tenantId);

        return DB::transaction(function () use ($data, $tenantId, $branchId) {
            $entry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'entry_date' => $data['entry_date'] ?? now()->format('Y-m-d'),
                'reference_no' => $data['reference_no'] ?? null,
                'description' => $data['description'] ?? '',
                'created_by' => auth()->id() ?? 1
            ]);

            $totalDebit = 0.00;
            $totalCredit = 0.00;

            foreach ($data['items'] as $item) {
                // Find account by code or ID
                $account = null;
                if (isset($item['account_code'])) {
                    $account = AccountChart::where('tenant_id', $tenantId)
                        ->where('account_code', $item['account_code'])
                        ->first();
                } else if (isset($item['account_id'])) {
                    $account = AccountChart::find($item['account_id']);
                }

                if (!$account) {
                    throw new Exception("Accounting Account not found mapping: " . ($item['account_code'] ?? $item['account_id']));
                }

                $debit = (float)($item['debit'] ?? 0.00);
                $credit = (float)($item['credit'] ?? 0.00);

                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $account->id,
                    'debit' => $debit,
                    'credit' => $credit
                ]);

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            // Verify balancing constraint (sum of debits == sum of credits)
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new Exception("Accounting Error: Debits (৳{$totalDebit}) must equal Credits (৳{$totalCredit}) to balance transaction journal.");
            }

            return $entry;
        });
    }

    /**
     * Get Profit & Loss Report data.
     */
    public function getProfitAndLoss(int $tenantId, ?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts_chart', 'journal_items.account_id', '=', 'accounts_chart.id')
            ->where('journal_entries.tenant_id', $tenantId);

        if ($branchId) {
            $query->where('journal_entries.branch_id', $branchId);
        }
        if ($startDate) {
            $query->where('journal_entries.entry_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('journal_entries.entry_date', '<=', $endDate);
        }

        $items = $query->select(
            'accounts_chart.account_name',
            'accounts_chart.account_code',
            'accounts_chart.account_type',
            DB::raw('SUM(journal_items.debit) as total_debit'),
            DB::raw('SUM(journal_items.credit) as total_credit')
        )->groupBy('accounts_chart.id')->get();

        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($items as $item) {
            $balance = 0;
            if ($item->account_type === 'revenue') {
                $balance = $item->total_credit - $item->total_debit; // Normal credit balance
                $revenues[] = [
                    'code' => $item->account_code,
                    'name' => $item->account_name,
                    'balance' => $balance
                ];
                $totalRevenue += $balance;
            } else if ($item->account_type === 'expense') {
                $balance = $item->total_debit - $item->total_credit; // Normal debit balance
                $expenses[] = [
                    'code' => $item->account_code,
                    'name' => $item->account_name,
                    'balance' => $balance
                ];
                $totalExpense += $balance;
            }
        }

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_profit' => $totalRevenue - $totalExpense
        ];
    }

    /**
     * Get Balance Sheet Report data.
     */
    public function getBalanceSheet(int $tenantId, ?int $branchId = null): array
    {
        $query = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts_chart', 'journal_items.account_id', '=', 'accounts_chart.id')
            ->where('journal_entries.tenant_id', $tenantId);

        if ($branchId) {
            $query->where('journal_entries.branch_id', $branchId);
        }

        $items = $query->select(
            'accounts_chart.account_name',
            'accounts_chart.account_code',
            'accounts_chart.account_type',
            DB::raw('SUM(journal_items.debit) as total_debit'),
            DB::raw('SUM(journal_items.credit) as total_credit')
        )->groupBy('accounts_chart.id')->get();

        $assets = [];
        $liabilities = [];
        $equity = [];
        
        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;

        foreach ($items as $item) {
            if ($item->account_type === 'asset') {
                $balance = $item->total_debit - $item->total_credit; // Normal debit balance
                $assets[] = ['code' => $item->account_code, 'name' => $item->account_name, 'balance' => $balance];
                $totalAssets += $balance;
            } else if ($item->account_type === 'liability') {
                $balance = $item->total_credit - $item->total_debit; // Normal credit balance
                $liabilities[] = ['code' => $item->account_code, 'name' => $item->account_name, 'balance' => $balance];
                $totalLiabilities += $balance;
            } else if ($item->account_type === 'equity') {
                $balance = $item->total_credit - $item->total_debit; // Normal credit balance
                $equity[] = ['code' => $item->account_code, 'name' => $item->account_name, 'balance' => $balance];
                $totalEquity += $balance;
            }
        }

        // Include retained earnings dynamically from total P&L history
        $plData = $this->getProfitAndLoss($tenantId, $branchId);
        $retainedEarnings = $plData['net_profit'];
        $equity[] = ['code' => '3100', 'name' => 'Retained Earnings (P&L)', 'balance' => $retainedEarnings];
        $totalEquity += $retainedEarnings;

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01
        ];
    }
}
