<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShiftAssignment;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EmployeeShiftAssignmentController extends Controller
{
    use ApiResponseTrait;

    public function getAssignments(int $employeeId): JsonResponse
    {
        try {
            $assignments = EmployeeShiftAssignment::with('shift')
                ->where('employee_id', $employeeId)
                ->orderBy('start_date', 'desc')
                ->get();
            return $this->successResponse($assignments, 'Employee shift assignments retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function assignShift(Request $request, int $employeeId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shift_id' => 'required|integer|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            // Deactivate previous active shift assignments
            EmployeeShiftAssignment::where('employee_id', $employeeId)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'end_date' => Carbon::parse($request->input('start_date'))->subDay()->format('Y-m-d')
                ]);

            // Create new assignment
            $assignment = EmployeeShiftAssignment::create([
                'employee_id' => $employeeId,
                'shift_id' => $request->input('shift_id'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'is_active' => true,
            ]);

            return $this->successResponse($assignment, 'Shift assigned to employee successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
