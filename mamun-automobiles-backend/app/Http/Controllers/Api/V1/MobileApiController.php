<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MobileAuthService;
use App\Services\OfflineSyncService;
use Illuminate\Http\Request;

class MobileApiController extends Controller
{
    protected $authService;
    protected $syncService;

    public function __construct(MobileAuthService $authService, OfflineSyncService $syncService)
    {
        $this->authService = $authService;
        $this->syncService = $syncService;
    }

    public function login(Request $request)
    {
        return response()->json($this->authService->login($request->all(), $request->header('User-Agent')));
    }

    public function sync(Request $request)
    {
        return response()->json($this->syncService->sync($request->header('Device-Token'), $request->all()));
    }

    public function dashboard()
    {
        return response()->json([
            'pending_jobs' => 12,
            'today_revenue' => 15000,
            'notifications' => 3
        ]);
    }

    public function loginCustomer(Request $request)
    {
        return response()->json(['success' => true, 'token' => 'mock-customer-token']);
    }

    public function loginStaff(Request $request)
    {
        return $this->login($request);
    }

    public function getCustomerServiceTracking(Request $request)
    {
        return response()->json([
            'success' => true,
            'status' => 'in_progress',
            'estimated_completion' => now()->addHours(4)->toIso8601String()
        ]);
    }

    public function getStaffWorkOrders(Request $request)
    {
        $employee = \App\Models\Employee::where('user_id', auth()->id())->first();
        if (!$employee) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $workOrders = \App\Models\WorkOrder::whereHas('jobCard.assignments', function ($q) use ($employee) {
            $q->where('employee_id', $employee->id)->where('status', 'active');
        })->with(['jobCard.vehicle', 'jobCard.tasks'])->get();

        return response()->json([
            'success' => true,
            'data' => $workOrders
        ]);
    }

    public function getTasks(Request $request)
    {
        $employee = \App\Models\Employee::where('user_id', auth()->id())->first();
        if (!$employee) {
            return response()->json(['success' => true, 'data' => []]);
        }

        // Fetch tasks assigned to this employee via JobTaskAssignment
        $assignments = \App\Models\JobTaskAssignment::with(['task.jobCard.vehicle', 'task.jobCard.workOrder'])
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['active', 'completed'])
            ->get();

        $tasks = $assignments->map(function ($assignment) {
            $task = $assignment->task;
            if (!$task) return null;
            $jobCard = $task->jobCard;
            $workOrder = $jobCard ? ($jobCard->workOrder ?? null) : null;

            return [
                'id' => $task->id,
                'task_name' => $task->name,
                'name' => $task->name,
                'status' => $task->status,
                'estimated_minutes' => $task->estimated_minutes,
                'actual_minutes' => $task->actual_minutes,
                'job_card_id' => $jobCard ? $jobCard->id : null,
                'work_order_id' => $workOrder ? $workOrder->id : null,
                'work_order' => $workOrder ? [
                    'id' => $workOrder->id,
                    'job_card' => [
                        'id' => $jobCard->id,
                        'vehicle' => $jobCard->vehicle ? [
                            'id' => $jobCard->vehicle->id,
                            'registration_no' => $jobCard->vehicle->registration_no ?? $jobCard->vehicle->plate_number ?? 'Unknown',
                            'make' => $jobCard->vehicle->make,
                            'model' => $jobCard->vehicle->model,
                        ] : null
                    ]
                ] : null
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    public function updateTaskStatus(Request $request, int $id)
    {
        $status = strtolower($request->input('status'));
        $task = \App\Models\JobCardTask::findOrFail($id);
        $employee = \App\Models\Employee::where('user_id', auth()->id())->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Authenticated user is not registered as an employee.'], 403);
        }

        $assignment = \App\Models\JobTaskAssignment::where('job_card_task_id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$assignment) {
            // Auto-assign task if technician updates its status but is not assigned yet
            $assignment = \App\Models\JobTaskAssignment::create([
                'job_card_task_id' => $id,
                'employee_id' => $employee->id,
                'allocated_at' => now(),
                'status' => 'active',
            ]);
        }

        if ($status === 'in_progress') {
            $task->update(['status' => 'in_progress']);
            $assignment->update(['status' => 'active']);
            event(new \App\Events\TaskStarted($task));
        } elseif ($status === 'paused') {
            $task->update(['status' => 'paused']);
            // Keep assignment active or mark paused if model supports it
        } elseif ($status === 'completed') {
            $assignment->update([
                'completed_at' => now(),
                'status' => 'completed',
            ]);
            $task->update(['status' => 'completed']);
            event(new \App\Events\TaskCompleted($task));
        } else {
            $task->update(['status' => $status]);
        }

        return response()->json([
            'success' => true,
            'message' => "Task status updated to {$status}",
            'data' => [
                'id' => $task->id,
                'status' => $task->status,
                'actual_minutes' => $task->actual_minutes,
            ]
        ]);
    }
}
