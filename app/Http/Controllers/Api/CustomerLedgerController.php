<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\CustomerLedgerService;

class CustomerLedgerController extends Controller
{
    protected $ledgerService;

    public function __construct(CustomerLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function index(Request $request)
    {
        $query = DB::table('customer_ledgers')
            ->join('customers', 'customer_ledgers.customer_id', '=', 'customers.id')
            ->select('customer_ledgers.*', 'customers.name', 'customers.phone');
            
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('customers.name', 'like', "%{$search}%")
                  ->orWhere('customers.phone', 'like', "%{$search}%");
        }

        return response()->json($query->paginate(15));
    }

    public function show($customer)
    {
        $ledger = DB::table('customer_ledgers')->where('customer_id', $customer)->first();
        if (!$ledger) {
            return response()->json(['message' => 'Ledger not found'], 404);
        }
        
        $customerData = DB::table('customers')->where('id', $customer)->first();
        return response()->json(['ledger' => $ledger, 'customer' => $customerData]);
    }

    public function statement(Request $request, $customer)
    {
        $query = DB::table('customer_transactions')
            ->where('customer_id', $customer)
            ->orderBy('created_at', 'asc');
            
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }
        
        return response()->json($query->get());
    }

    public function recordPayment(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
            'invoice_id' => 'nullable|exists:invoices,id'
        ]);

        $this->ledgerService->recordPayment(
            $validated['customer_id'],
            $validated['amount'],
            $validated['note'] ?? 'Payment Received',
            $validated['invoice_id'] ?? null
        );

        return response()->json(['message' => 'Payment recorded successfully']);
    }
}
