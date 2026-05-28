<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeAvailability;
use App\Constants\WorkforceConstants;
use Carbon\Carbon;

class WorkforceAvailabilityService
{
    /**
     * Check if an employee is available for assignment on a given date.
     */
    public function isAvailable(Employee $employee, ?string $dateStr = null): bool
    {
        $date = $dateStr ? Carbon::parse($dateStr) : Carbon::today();

        // 1. Employee overall employment lifecycle check
        if ($employee->status !== WorkforceConstants::STATUS_ACTIVE) {
            return false;
        }

        // 2. Real-time availability status check
        if (in_array($employee->availability_status, [
            WorkforceConstants::AVAIL_LEAVE,
            WorkforceConstants::AVAIL_OFFLINE
        ])) {
            return false;
        }

        // 3. Date-specific logs check
        $availabilityOverride = EmployeeAvailability::where('employee_id', $employee->id)
            ->whereDate('date', $date->format('Y-m-d'))
            ->first();

        if ($availabilityOverride && !$availabilityOverride->is_available) {
            return false;
        }

        // 4. Shift alignment check
        $hasActiveShift = $employee->shiftAssignments()
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $date->format('Y-m-d'));
            })
            ->exists();

        if (!$hasActiveShift) {
            return false;
        }

        // 5. Workload threshold limit (Overload check - max 5 active assignments)
        $activeAssignmentsCount = $employee->jobCardAssignments()
            ->where('status', 'active')
            ->count();

        if ($activeAssignmentsCount >= 5) {
            return false;
        }

        return true;
    }
}
