<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CashReconciliation;
use App\Services\CashReconciliationService;
use Illuminate\Http\Request;

class CashReconciliationController extends Controller
{
    protected $reconciliationService;

    public function __construct(CashReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    public function index(Request $request)
    {
        $query = CashReconciliation::with('cashbook');
        
        if ($request->has('cashbook_id')) {
            $query->where('cashbook_id', $request->cashbook_id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cashbook_id' => 'required|exists:cashbooks,id',
            'actual_balance' => 'required|numeric',
            'notes' => 'nullable|string'
        ]);

        try {
            $reconciliation = $this->reconciliationService->reconcile(
                $request->cashbook_id,
                $request->actual_balance,
                $request->notes
            );
            return response()->json($reconciliation, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
