<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cashbook;
use App\Models\CashbookTransaction;
use App\Services\CashbookService;
use Illuminate\Http\Request;

class CashbookController extends Controller
{
    protected $cashbookService;

    public function __construct(CashbookService $cashbookService)
    {
        $this->cashbookService = $cashbookService;
    }

    public function index(Request $request)
    {
        $query = Cashbook::query();
        
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:main_cash,bank,mobile_banking,petty_cash',
            'opening_balance' => 'numeric'
        ]);

        $cashbook = Cashbook::create([
            'name' => $request->name,
            'type' => $request->type,
            'opening_balance' => $request->opening_balance ?? 0,
            'current_balance' => $request->opening_balance ?? 0,
            'branch_id' => $request->branch_id,
            'created_by' => auth()->id() ?? 1,
            'status' => true
        ]);

        return response()->json($cashbook, 201);
    }

    public function transactions(Request $request)
    {
        $query = CashbookTransaction::with('cashbook');
        
        if ($request->has('cashbook_id')) {
            $query->where('cashbook_id', $request->cashbook_id);
        }

        if ($request->has('type')) {
            $query->where('transaction_type', $request->type);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'from_cashbook_id' => 'required|exists:cashbooks,id',
            'to_cashbook_id' => 'required|exists:cashbooks,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string'
        ]);

        try {
            $this->cashbookService->transfer(
                $request->from_cashbook_id,
                $request->to_cashbook_id,
                $request->amount,
                $request->description
            );
            return response()->json(['message' => 'Transfer successful']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
