<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Leave;
use App\Traits\ApiResponseTrait;

class HrController extends Controller
{
    use ApiResponseTrait;

    public function attendances(Request $request): JsonResponse
    {
        $query = Attendance::with('user:id,name,email');
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }
        return $this->successResponse($query->orderBy('date', 'desc')->get(), 'Attendances retrieved successfully');
    }

    public function payrolls(Request $request): JsonResponse
    {
        $query = Payroll::with('user:id,name,email');
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('month') && $request->has('year')) {
            $query->where('month', $request->month)->where('year', $request->year);
        }
        return $this->successResponse($query->orderBy('created_at', 'desc')->get(), 'Payrolls retrieved successfully');
    }

    public function leaves(Request $request): JsonResponse
    {
        $query = Leave::with('user:id,name,email');
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        return $this->successResponse($query->orderBy('created_at', 'desc')->get(), 'Leaves retrieved successfully');
    }
}

