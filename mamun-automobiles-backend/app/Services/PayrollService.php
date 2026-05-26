<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payroll;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollService
{
    public function generateMonthlyPayroll($month, $year)
    {
        $users = User::all();
        $generated = 0;

        foreach ($users as $user) {
            // Check if already generated
            $existing = Payroll::where('user_id', $user->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existing) continue;

            $salaryStructure = DB::table('salary_structures')->where('user_id', $user->id)->first();
            $basicSalary = $salaryStructure ? $salaryStructure->gross_salary : 0;

            // Calculate attendance stats
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $totalOvertimeMinutes = $attendances->sum('overtime_minutes');
            $totalLateMinutes = $attendances->sum('late_minutes');
            $absentCount = $attendances->where('status', 'absent')->count();

            // Daily rate approximation
            $dailyRate = $basicSalary > 0 ? $basicSalary / 30 : 0;
            $hourlyRate = $dailyRate / 8;

            // Calculations
            $overtimePay = ($totalOvertimeMinutes / 60) * ($hourlyRate * 1.5); // Overtime at 1.5x
            $lateDeduction = ($totalLateMinutes / 60) * $hourlyRate;
            $absentDeduction = $absentCount * $dailyRate;

            // Advances
            $advances = DB::table('salary_advances')
                ->where('user_id', $user->id)
                ->where('is_deducted', false)
                ->sum('amount');

            // Tax (simple 5% if basic > 30000)
            $tax = $basicSalary > 30000 ? ($basicSalary * 0.05) : 0;

            $totalDeductions = $lateDeduction + $absentDeduction + $tax;
            $netSalary = $basicSalary + $overtimePay - $totalDeductions - $advances;

            if ($netSalary < 0) $netSalary = 0;

            $payroll = Payroll::create([
                'user_id' => $user->id,
                'month' => $month,
                'year' => $year,
                'payroll_cycle' => 'monthly',
                'basic_salary' => $basicSalary,
                'overtime_amount' => $overtimePay,
                'bonus' => 0, // Manual addition later
                'late_deduction' => $lateDeduction,
                'absent_deduction' => $absentDeduction,
                'advances' => $advances,
                'tax' => $tax,
                'deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'status' => 'draft'
            ]);

            // Mark advances as deducted
            DB::table('salary_advances')
                ->where('user_id', $user->id)
                ->where('is_deducted', false)
                ->update(['is_deducted' => true, 'payroll_id' => $payroll->id]);

            $generated++;
        }

        return ['status' => 'success', 'generated_count' => $generated];
    }
}
