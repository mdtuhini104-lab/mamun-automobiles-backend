<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WorkforceAssignmentService;
use App\Models\EmployeeAvailability;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeAvailabilityController extends Controller
{
    use ApiResponseTrait;

    protected $assignmentService;

    public function __construct(WorkforceAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function getHistory(int $employeeId): JsonResponse
    {
        try {
            $history = EmployeeAvailability::where('employee_id', $employeeId)
                ->latest()
                ->get();
            return $this->successResponse($history, 'Employee availability history retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function updateAvailability(Request $request, int $employeeId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:available,busy,assigned,on_leave,offline',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            $this->assignmentService->updateEmployeeAvailability(
                $employeeId,
                $request->input('status'),
                $request->input('notes')
            );
            return $this->successResponse(null, 'Employee availability updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
