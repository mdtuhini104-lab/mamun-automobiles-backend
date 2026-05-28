<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WorkforceAssignmentService;
use App\Services\WorkshopBayService;
use App\Http\Resources\JobCardAssignmentResource;
use App\Http\Resources\JobCardTaskResource;
use App\Http\Resources\JobTaskAssignmentResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkforceAssignmentController extends Controller
{
    use ApiResponseTrait;

    protected $assignmentService;
    protected $bayService;

    public function __construct(WorkforceAssignmentService $assignmentService, WorkshopBayService $bayService)
    {
        $this->assignmentService = $assignmentService;
        $this->bayService = $bayService;
    }

    /**
     * Assign lead technician, assistants, helpers, and optionally a workshop bay.
     */
    public function assignWorkforce(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lead_technician_id' => 'nullable|integer|exists:employees,id',
            'assistant_technician_ids' => 'nullable|array',
            'assistant_technician_ids.*' => 'integer|exists:employees,id',
            'helper_ids' => 'nullable|array',
            'helper_ids.*' => 'integer|exists:employees,id',
            'workshop_bay_id' => 'nullable|integer|exists:workshop_bays,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            // Allocate workshop bay if provided
            if ($request->has('workshop_bay_id') && $request->input('workshop_bay_id') !== null) {
                $this->bayService->allocateBay($id, $request->input('workshop_bay_id'));
            }

            // Assign technicians and helpers
            $assignments = $this->assignmentService->assignWorkforce($id, $request->only([
                'lead_technician_id',
                'assistant_technician_ids',
                'helper_ids',
            ]));

            return $this->successResponse(
                JobCardAssignmentResource::collection($assignments),
                'Workforce assigned successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Create a task for a Job Card.
     */
    public function createTask(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_minutes' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            $task = $this->assignmentService->createTask($id, $request->only([
                'name',
                'description',
                'estimated_minutes',
            ]));

            return $this->successResponse(
                new JobCardTaskResource($task),
                'Task created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Allocate employee to task.
     */
    public function assignTask(Request $request, int $id, int $taskId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            $assignment = $this->assignmentService->assignTask($taskId, $request->input('employee_id'));

            return $this->successResponse(
                new JobTaskAssignmentResource($assignment),
                'Employee assigned to task successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Complete task assignment.
     */
    public function completeTaskAssignment(int $assignmentId): JsonResponse
    {
        try {
            $this->assignmentService->completeTaskAssignment($assignmentId);
            return $this->successResponse(null, 'Task assignment completed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Complete job card assignment and input labor hours.
     */
    public function completeAssignment(Request $request, int $assignmentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'labor_hours' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            $this->assignmentService->completeAssignment(
                $assignmentId,
                $request->input('labor_hours')
            );
            return $this->successResponse(null, 'Assignment completed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Get workload metrics for a technician.
     */
    public function getEmployeeWorkload(int $employeeId): JsonResponse
    {
        try {
            $metrics = $this->assignmentService->getWorkloadMetrics($employeeId);
            return $this->successResponse($metrics, 'Workload metrics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * List all active employees with filters and workload indicators.
     */
    public function listEmployees(Request $request): JsonResponse
    {
        $query = \App\Models\Employee::where('status', \App\Constants\WorkforceConstants::STATUS_ACTIVE)
            ->with(['user', 'department', 'designation', 'skills', 'jobCardAssignments' => function ($q) {
                $q->where('status', 'active');
            }]);

        if ($request->has('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->has('designation_id')) {
            $query->where('designation_id', $request->input('designation_id'));
        }

        if ($request->has('skill_id')) {
            $query->whereHas('skills', function ($q) use ($request) {
                $q->where('skills.id', $request->input('skill_id'));
            });
        }

        if ($request->has('availability')) {
            $query->where('availability_status', $request->input('availability'));
        }

        $employees = $query->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'phone' => $employee->phone,
                'status' => $employee->status,
                'availability_status' => $employee->availability_status,
                'active_jobs_count' => $employee->jobCardAssignments->count(),
                'department' => $employee->department ? [
                    'id' => $employee->department->id,
                    'name' => $employee->department->name,
                    'code' => $employee->department->code,
                ] : null,
                'designation' => $employee->designation ? [
                    'id' => $employee->designation->id,
                    'name' => $employee->designation->name,
                    'code' => $employee->designation->code,
                ] : null,
                'skills' => $employee->skills->map(function ($skill) {
                    return [
                        'id' => $skill->id,
                        'name' => $skill->name,
                        'code' => $skill->code,
                        'proficiency_level' => $skill->pivot->proficiency_level,
                    ];
                }),
            ];
        });

        return $this->successResponse($employees, 'Employees retrieved successfully');
    }

    /**
     * List all skills.
     */
    public function listSkills(): JsonResponse
    {
        $skills = \App\Models\Skill::orderBy('name')->get();
        return $this->successResponse($skills, 'Skills retrieved successfully');
    }

    /**
     * List active departments.
     */
    public function listDepartments(): JsonResponse
    {
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        return $this->successResponse($departments, 'Departments retrieved successfully');
    }

    /**
     * List active designations.
     */
    public function listDesignations(): JsonResponse
    {
        $designations = \App\Models\Designation::where('is_active', true)->orderBy('name')->get();
        return $this->successResponse($designations, 'Designations retrieved successfully');
    }
}

