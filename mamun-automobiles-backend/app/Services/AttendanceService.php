<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function recordCheckIn(User $user, $deviceInfo = null, $ipAddress = null)
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        // Check if already checked in
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            return ['status' => 'error', 'message' => 'Already checked in today'];
        }

        // Determine Shift
        $shift = $user->shift ?? Shift::first(); // Fallback to first shift if none assigned
        $lateMinutes = 0;
        $status = 'present';

        if ($shift) {
            $shiftStart = Carbon::parse($shift->start_time);
            $checkInTime = Carbon::parse($now);
            
            if ($checkInTime->diffInMinutes($shiftStart, false) < -($shift->late_threshold_minutes)) {
                $lateMinutes = $checkInTime->diffInMinutes($shiftStart->addMinutes($shift->late_threshold_minutes));
                $status = 'late';
            }
        }

        // Check if today is a holiday
        $isHoliday = Holiday::where('start_date', '<=', $today)->where('end_date', '>=', $today)->exists();
        if ($isHoliday) {
            $status = 'holiday_overtime'; // Custom status for working on holiday
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => $now,
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'device_info' => $deviceInfo,
            'ip_address' => $ipAddress
        ]);

        return ['status' => 'success', 'message' => 'Checked in successfully', 'data' => $attendance];
    }

    public function recordCheckOut(User $user, $deviceInfo = null, $ipAddress = null)
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return ['status' => 'error', 'message' => 'No check-in record found for today'];
        }

        if ($attendance->check_out) {
            return ['status' => 'error', 'message' => 'Already checked out today'];
        }

        $checkIn = Carbon::parse($attendance->check_in);
        $workMinutes = $now->diffInMinutes($checkIn);
        $workHours = round($workMinutes / 60, 2);

        $shift = $user->shift ?? Shift::first();
        $overtimeMinutes = 0;

        if ($shift) {
            $shiftDuration = Carbon::parse($shift->end_time)->diffInMinutes(Carbon::parse($shift->start_time));
            if ($workMinutes > $shiftDuration) {
                $overtimeMinutes = $workMinutes - $shiftDuration;
            }
            
            // Half day detection
            if ($workMinutes < ($shiftDuration / 2)) {
                $attendance->status = 'half_day';
            }
        }

        $attendance->update([
            'check_out' => $now->toTimeString(),
            'work_hours' => $workHours,
            'overtime_minutes' => $overtimeMinutes
        ]);

        return ['status' => 'success', 'message' => 'Checked out successfully', 'data' => $attendance];
    }
}
