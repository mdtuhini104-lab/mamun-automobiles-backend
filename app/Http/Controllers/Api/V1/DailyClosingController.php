<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyClosing;
use App\Services\DailyClosingService;
use Illuminate\Http\Request;

class DailyClosingController extends Controller
{
    protected $dailyClosingService;

    public function __construct(DailyClosingService $dailyClosingService)
    {
        $this->dailyClosingService = $dailyClosingService;
    }

    public function index(Request $request)
    {
        $query = DailyClosing::with('cashbook');
        
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function close(Request $request)
    {
        $request->validate([
            'cashbook_id' => 'required|exists:cashbooks,id',
            'manual_adjustment' => 'numeric',
            'closing_notes' => 'nullable|string'
        ]);

        try {
            $closing = $this->dailyClosingService->closeDay(
                $request->cashbook_id,
                $request->manual_adjustment ?? 0,
                $request->closing_notes ?? ''
            );
            return response()->json($closing, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
