<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\JobCard;
use App\Models\JobCardAssignment;
use App\Models\JobCardTask;
use App\Models\JobTaskAssignment;
use App\Models\EmployeeAvailability;
use App\Models\EmployeeShiftAssignment;
use App\Models\WorkshopBay;
use App\Constants\WorkforceConstants;
use App\Services\WorkforceAvailabilityService;
use App\Services\ActivityLogService;
use App\Events\AssignmentCreated;
use App\Events\AssignmentCompleted;
use App\Events\TaskAssigned;
use App\Events\AvailabilityChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WorkforceAssignmentService
{
    protected $availabilityService;

    public function __construct(WorkforceAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Assign technicians and helper to a job card.
     */
    public function assignWorkforce(int $jobCardId, array $data): array
    {
        $correlationId = Str::uuid()->toString();

        return DB::transaction(function () use ($jobCardId, $data, $correlationId) {
            $jobCard = JobCard::findOrFail($jobCardId);
            $newAssignments = [];

            // 1. Validate and map lead technician
            $leadTechId = $data['lead_technician_id'] ?? null;
            if ($leadTechId) {
                $leadEmployee = Employee::findOrFail($leadTechId);
                
                // Validate availability
                if (!$this->availabilityService->isAvailable($leadEmployee)) {
                    throw new \Exception("Lead technician (ID: {$leadTechId}) is not available for assignment.");
                }

                // Protect uniqueness: transition previous active assignment if it exists
                $this->transitionActiveAssignment($jobCardId, $leadTechId, 'reassigned', $correlationId);

                // Create new lead technician assignment
                $assignment = JobCardAssignment::create([
                    'job_card_id' => $jobCardId,
                    'employee_id' => $leadTechId,
                    'assignment_type' => WorkforceConstants::ASSIGN_LEAD,
                    'started_at' => Carbon::now(),
                    'status' => 'active',
                ]);

                // Sync with legacy assigned_mechanic_id on Job Card for backward compatibility
                $jobCard->assigned_mechanic_id = $leadEmployee->user_id;
                $jobCard->save();

                // Update employee status to assigned
                $this->updateEmployeeStatus($leadEmployee, WorkforceConstants::AVAIL_ASSIGNED, $correlationId);

                event(new AssignmentCreated($assignment));
                $newAssignments[] = $assignment;

                ActivityLogService::log(
                    module: 'Workforce',
                    action: 'assign_lead',
                    description: "Employee {$leadEmployee->employee_code} assigned as Lead Technician on Job Card #{$jobCardId}",
                    oldValues: null,
                    newValues: ['assignment_id' => $assignment->id, 'correlation_id' => $correlationId],
                    severity: 'info'
                );
            }

            // 2. Validate and map assistant technicians
            $assistantIds = $data['assistant_technician_ids'] ?? [];
            foreach ($assistantIds as $asstId) {
                $asstEmployee = Employee::findOrFail($asstId);
                
                if (!$this->availabilityService->isAvailable($asstEmployee)) {
                    throw new \Exception("Assistant technician (ID: {$asstId}) is not available for assignment.");
                }

                $this->transitionActiveAssignment($jobCardId, $asstId, 'reassigned', $correlationId);

                $assignment = JobCardAssignment::create([
                    'job_card_id' => $jobCardId,
                    'employee_id' => $asstId,
                    'assignment_type' => WorkforceConstants::ASSIGN_ASSISTANT,
                    'started_at' => Carbon::now(),
                    'status' => 'active',
                ]);

                $this->updateEmployeeStatus($asstEmployee, WorkforceConstants::AVAIL_ASSIGNED, $correlationId);

                event(new AssignmentCreated($assignment));
                $newAssignments[] = $assignment;

                ActivityLogService::log(
                    module: 'Workforce',
                    action: 'assign_assistant',
                    description: "Employee {$asstEmployee->employee_code} assigned as Assistant on Job Card #{$jobCardId}",
                    oldValues: null,
                    newValues: ['assignment_id' => $assignment->id, 'correlation_id' => $correlationId],
                    severity: 'info'
                );
            }

            // 3. Validate and map helpers
            $helperIds = $data['helper_ids'] ?? [];
            foreach ($helperIds as $helperId) {
                $helperEmployee = Employee::findOrFail($helperId);
                
                if (!$this->availabilityService->isAvailable($helperEmployee)) {
                    throw new \Exception("Helper (ID: {$helperId}) is not available.");
                }

                $this->transitionActiveAssignment($jobCardId, $helperId, 'reassigned', $correlationId);

                $assignment = JobCardAssignment::create([
                    'job_card_id' => $jobCardId,
                    'employee_id' => $helperId,
                    'assignment_type' => WorkforceConstants::ASSIGN_HELPER,
                    'started_at' => Carbon::now(),
                    'status' => 'active',
                ]);

                $this->updateEmployeeStatus($helperEmployee, WorkforceConstants::AVAIL_ASSIGNED, $correlationId);

                event(new AssignmentCreated($assignment));
                $newAssignments[] = $assignment;

                ActivityLogService::log(
                    module: 'Workforce',
                    action: 'assign_helper',
                    description: "Employee {$helperEmployee->employee_code} assigned as Helper on Job Card #{$jobCardId}",
                    oldValues: null,
                    newValues: ['assignment_id' => $assignment->id, 'correlation_id' => $correlationId],
                    severity: 'info'
                );
            }

            return $newAssignments;
        });
    }

    /**
     * Create a task for a Job Card.
     */
    public function createTask(int $jobCardId, array $data): JobCardTask
    {
        return DB::transaction(function () use ($jobCardId, $data) {
            $task = JobCardTask::create([
                'job_card_id' => $jobCardId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'estimated_minutes' => $data['estimated_minutes'] ?? 0,
                'status' => 'pending',
            ]);

            ActivityLogService::log(
                module: 'Workforce',
                action: 'create_task',
                description: "Task '{$task->name}' created for Job Card #{$jobCardId}",
                oldValues: null,
                newValues: $task->toArray()
            );

            return $task;
        });
    }

    /**
     * Allocate a specific employee to a Job Card Task.
     */
    public function assignTask(int $taskId, int $employeeId): JobTaskAssignment
    {
        $correlationId = Str::uuid()->toString();

        return DB::transaction(function () use ($taskId, $employeeId, $correlationId) {
            $task = JobCardTask::findOrFail($taskId);
            $employee = Employee::findOrFail($employeeId);

            // 1. Check availability
            if (!$this->availabilityService->isAvailable($employee)) {
                throw new \Exception("Employee is not available to receive task assignments.");
            }

            // 2. Protect uniqueness: check duplicate active task assignments
            $exists = JobTaskAssignment::where('job_card_task_id', $taskId)
                ->where('employee_id', $employeeId)
                ->where('status', 'active')
                ->exists();

            if ($exists) {
                throw new \Exception("Employee already has an active assignment for this task.");
            }

            // 3. Create task assignment
            $taskAssignment = JobTaskAssignment::create([
                'job_card_task_id' => $taskId,
                'employee_id' => $employeeId,
                'allocated_at' => Carbon::now(),
                'status' => 'active',
            ]);

            // Update task status to in_progress
            if ($task->status === 'pending') {
                $task->update(['status' => 'in_progress']);
            }

            // Employeeavailability becomes busy if they take on tasks
            $this->updateEmployeeStatus($employee, WorkforceConstants::AVAIL_BUSY, $correlationId);

            event(new TaskAssigned($taskAssignment));

            ActivityLogService::log(
                module: 'Workforce',
                action: 'assign_task',
                description: "Employee {$employee->employee_code} assigned to Task #{$taskId}",
                oldValues: null,
                newValues: ['task_assignment_id' => $taskAssignment->id, 'correlation_id' => $correlationId]
            );

            return $taskAssignment;
        });
    }

    /**
     * Complete a task assignment.
     */
    public function completeTaskAssignment(int $assignmentId): bool
    {
        return DB::transaction(function () use ($assignmentId) {
            $assignment = JobTaskAssignment::findOrFail($assignmentId);
            
            if ($assignment->status !== 'active') {
                return true;
            }

            // Validation: completed_at cannot precede allocated_at
            $completedAt = Carbon::now();
            if ($completedAt->lt($assignment->allocated_at)) {
                throw new \Exception('Completed timestamp cannot precede allocation timestamp.');
            }

            $assignment->update([
                'completed_at' => $completedAt,
                'status' => 'completed',
            ]);

            // If all task assignments for this task are completed, mark task as completed
            $task = $assignment->task;
            $hasActive = JobTaskAssignment::where('job_card_task_id', $task->id)
                ->where('status', 'active')
                ->exists();

            if (!$hasActive) {
                $task->update(['status' => 'completed']);
            }

            // Update employee availability back to available if no other busy assignments
            $this->recomputeEmployeeAvailability($assignment->employee);

            ActivityLogService::log(
                module: 'Workforce',
                action: 'complete_task_assignment',
                description: "Task assignment #{$assignmentId} marked as completed",
                oldValues: ['status' => 'active'],
                newValues: ['status' => 'completed']
            );

            return true;
        });
    }

    /**
     * Complete a job card assignment (Immutability rules apply).
     */
    public function completeAssignment(int $assignmentId, float $laborHours): bool
    {
        if ($laborHours < 0) {
            throw new \Exception('Labor hours must never become negative.');
        }

        return DB::transaction(function () use ($assignmentId, $laborHours) {
            $assignment = JobCardAssignment::findOrFail($assignmentId);
            
            if ($assignment->status !== 'active') {
                return true;
            }

            $endedAt = Carbon::now();
            if ($endedAt->lt($assignment->started_at)) {
                throw new \Exception('Ended timestamp cannot precede started timestamp.');
            }

            // Mark completed and immutable
            $assignment->update([
                'ended_at' => $endedAt,
                'labor_hours' => $laborHours,
                'status' => 'completed',
            ]);

            $this->recomputeEmployeeAvailability($assignment->employee);

            event(new AssignmentCompleted($assignment));

            ActivityLogService::log(
                module: 'Workforce',
                action: 'complete_assignment',
                description: "Job card assignment #{$assignmentId} completed with {$laborHours} hours",
                oldValues: ['status' => 'active'],
                newValues: ['status' => 'completed', 'labor_hours' => $laborHours]
            );

            return true;
        });
    }

    /**
     * Update employee availability status.
     */
    public function updateEmployeeAvailability(int $employeeId, string $status, ?string $notes = null): bool
    {
        $correlationId = Str::uuid()->toString();
        $employee = Employee::findOrFail($employeeId);

        return DB::transaction(function () use ($employee, $status, $notes, $correlationId) {
            $oldStatus = $employee->availability_status;

            // Update employee profile
            $employee->availability_status = $status;
            $employee->save();

            // Create availability log
            EmployeeAvailability::create([
                'employee_id' => $employee->id,
                'date' => Carbon::today(),
                'status' => $status,
                'is_available' => !in_array($status, [WorkforceConstants::AVAIL_LEAVE, WorkforceConstants::AVAIL_OFFLINE]),
                'notes' => $notes,
            ]);

            event(new AvailabilityChanged($employee, $status));

            ActivityLogService::log(
                module: 'Workforce',
                action: 'update_availability',
                description: "Employee {$employee->employee_code} availability status updated to {$status}",
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $status, 'correlation_id' => $correlationId]
            );

            return true;
        });
    }

    /**
     * Compute and fetch AI workload metrics.
     */
    public function getWorkloadMetrics(int $employeeId): array
    {
        $employee = Employee::findOrFail($employeeId);

        $activeJobsCount = JobCardAssignment::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->count();

        $activeTasksCount = JobTaskAssignment::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->count();

        $estimatedMinutes = JobTaskAssignment::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->join('job_card_tasks', 'job_task_assignments.job_card_task_id', '=', 'job_card_tasks.id')
            ->sum('job_card_tasks.estimated_minutes');

        return [
            'active_jobs_count' => $activeJobsCount,
            'active_tasks_count' => $activeTasksCount,
            'estimated_workload_minutes' => (int) $estimatedMinutes,
        ];
    }

    /**
     * Get Workshop Bay utilization.
     */
    public function getBayUtilization(int $bayId): array
    {
        $bay = WorkshopBay::findOrFail($bayId);
        
        $utilization = 0;
        if ($bay->max_vehicle_capacity > 0) {
            $utilization = ($bay->current_load / $bay->max_vehicle_capacity) * 100;
        }

        return [
            'max_capacity' => $bay->max_vehicle_capacity,
            'current_load' => $bay->current_load,
            'utilization_percentage' => round($utilization, 2),
        ];
    }

    // --- Private Helper Methods ---

    /**
     * Transition any active assignments for the employee to a new status.
     */
    private function transitionActiveAssignment(int $jobCardId, int $employeeId, string $newStatus, string $correlationId)
    {
        $activeAssignment = JobCardAssignment::where('job_card_id', $jobCardId)
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->first();

        if ($activeAssignment) {
            $endedAt = Carbon::now();
            $activeAssignment->update([
                'ended_at' => $endedAt,
                'status' => $newStatus,
            ]);

            event(new AssignmentCompleted($activeAssignment));

            ActivityLogService::log(
                module: 'Workforce',
                action: 'reassign_transition',
                description: "Active assignment #{$activeAssignment->id} transitioned to {$newStatus} due to reassignment",
                oldValues: ['status' => 'active'],
                newValues: ['status' => $newStatus, 'correlation_id' => $correlationId],
                severity: 'info'
            );
        }
    }

    /**
     * Helper to update employee availability status.
     */
    private function updateEmployeeStatus(Employee $employee, string $status, string $correlationId)
    {
        if ($employee->availability_status !== $status) {
            $oldStatus = $employee->availability_status;
            $employee->availability_status = $status;
            $employee->save();

            EmployeeAvailability::create([
                'employee_id' => $employee->id,
                'date' => Carbon::today(),
                'status' => $status,
                'is_available' => !in_array($status, [WorkforceConstants::AVAIL_LEAVE, WorkforceConstants::AVAIL_OFFLINE]),
                'notes' => 'Automatic status change via assignment update.',
            ]);

            event(new AvailabilityChanged($employee, $status));
        }
    }

    /**
     * Recompute employee availability based on active work.
     */
    private function recomputeEmployeeAvailability(Employee $employee)
    {
        // Check if employee has active tasks
        $hasActiveTasks = JobTaskAssignment::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->exists();

        if ($hasActiveTasks) {
            $this->updateEmployeeStatus($employee, WorkforceConstants::AVAIL_BUSY, 'recompute');
            return;
        }

        // Check if employee has active job card assignments
        $hasActiveJobs = JobCardAssignment::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->exists();

        if ($hasActiveJobs) {
            $this->updateEmployeeStatus($employee, WorkforceConstants::AVAIL_ASSIGNED, 'recompute');
            return;
        }

        // Else they are available
        $this->updateEmployeeStatus($employee, WorkforceConstants::AVAIL_AVAILABLE, 'recompute');
    }
}
