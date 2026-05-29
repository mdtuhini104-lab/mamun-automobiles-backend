<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\WorkshopBay;
use App\Models\Part;

class WorkflowSuggestionsService
{
    private function calculateBurnoutRisk(int $employeeId, ?int $userId): array
    {
        if (!$userId) {
            return ['score' => 0.0, 'risk_level' => 'Low', 'overtime' => 0, 'late_ratio' => 0];
        }

        // 1. Overtime hours (based on check_in and check_out in attendances)
        $attendances = DB::table('attendances')
            ->where('user_id', $userId)
            ->where('date', '>=', now()->subDays(30))
            ->get();

        $overtimeHours = 0;
        foreach ($attendances as $attendance) {
            if ($attendance->check_in && $attendance->check_out) {
                $in = \Carbon\Carbon::parse($attendance->check_in);
                $out = \Carbon\Carbon::parse($attendance->check_out);
                $hoursWorked = $out->diffInMinutes($in) / 60;
                if ($hoursWorked > 8) {
                    $overtimeHours += ($hoursWorked - 8);
                }
            }
        }

        // 2. Excessive active task ratios
        $activeTasksCount = DB::table('job_task_assignments')
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->count();

        // 3. Repeated late completions
        $totalCompletedTasks = DB::table('job_task_assignments')
            ->join('job_card_tasks', 'job_task_assignments.job_card_task_id', '=', 'job_card_tasks.id')
            ->where('job_task_assignments.employee_id', $employeeId)
            ->where('job_task_assignments.status', 'completed')
            ->where('job_task_assignments.updated_at', '>=', now()->subDays(30))
            ->count();

        $lateCompletions = DB::table('job_task_assignments')
            ->join('job_card_tasks', 'job_task_assignments.job_card_task_id', '=', 'job_card_tasks.id')
            ->where('job_task_assignments.employee_id', $employeeId)
            ->where('job_task_assignments.status', 'completed')
            ->where('job_task_assignments.updated_at', '>=', now()->subDays(30))
            ->whereRaw('job_card_tasks.actual_minutes > job_card_tasks.estimated_minutes')
            ->count();

        $lateRatio = $totalCompletedTasks > 0 ? ($lateCompletions / $totalCompletedTasks) : 0;

        // 4. Recovery factor (total active task time vs standard hours)
        $totalActiveMins = DB::table('job_task_assignments')
            ->join('job_card_tasks', 'job_task_assignments.job_card_task_id', '=', 'job_card_tasks.id')
            ->where('job_task_assignments.employee_id', $employeeId)
            ->where('job_task_assignments.updated_at', '>=', now()->subDays(7))
            ->sum('job_card_tasks.actual_minutes') ?: 0;

        $recoveryFactor = max(0, 1.0 - ($totalActiveMins / 2880));

        $score = ($overtimeHours * 2.0) + ($activeTasksCount * 15.0) + ($lateRatio * 30.0) + ((1.0 - $recoveryFactor) * 35.0);
        $score = min(100.0, max(0.0, round($score, 1)));

        $riskLevel = $score > 75 ? 'Critical' : ($score > 45 ? 'Medium' : 'Low');

        return [
            'score' => $score,
            'risk_level' => $riskLevel,
            'overtime' => $overtimeHours,
            'late_ratio' => $lateRatio
        ];
    }

    /**
     * Get suggestions for a new or existing Job Card.
     */
    public function getSuggestions(int $tenantId, ?int $vehicleId = null): array
    {
        // 1. Mechanic suggestions based on current workload (active assignments count)
        $employees = DB::table('employees')
            ->where('tenant_id', $tenantId)
            ->get();

        $activeAssignments = DB::table('job_card_assignments')
            ->where('status', 'active')
            ->select('employee_id', DB::raw('count(*) as count'))
            ->groupBy('employee_id')
            ->pluck('count', 'employee_id')
            ->toArray();

        $mechanics = $employees->map(function ($emp) use ($activeAssignments) {
            $count = $activeAssignments[$emp->id] ?? 0;
            
            // Burnout calculation
            $burnout = $this->calculateBurnoutRisk($emp->id, $emp->user_id);
            
            // Comeback ratio
            $metrics = DB::table('technician_metrics')
                ->where('employee_id', $emp->user_id)
                ->orderBy('date', 'desc')
                ->first();
            $comebackRatio = $metrics ? (float)$metrics->comeback_ratio : 0.00;

            // Skills / Specialization check
            $hasSkills = DB::table('employee_skills')
                ->where('employee_id', $emp->id)
                ->exists();

            // Routing suitability calculations
            $workloadScore = max(0, 100 - ($count * 25));
            $burnoutScore = 100 - $burnout['score'];
            $comebackScore = max(0, 100 - ($comebackRatio * 100));
            $skillsScore = $hasSkills ? 100 : 50;

            $suitabilityScore = ($workloadScore * 0.40) + ($burnoutScore * 0.30) + ($comebackScore * 0.20) + ($skillsScore * 0.10);
            $suitabilityScore = min(100.0, max(0.0, round($suitabilityScore, 1)));

            // Recommendation text to protect overloading top-performers and balance workload
            $recText = "Assign to balance load. ";
            if ($suitabilityScore > 85) {
                $recText .= "Highly recommended: optimal load, low fatigue risk, and good record.";
            } elseif ($burnout['score'] > 60) {
                $recText .= "Caution: Technician fatigue is elevated. Consider assigning to another worker.";
            } elseif ($count > 2) {
                $recText .= "Caution: Technician has high active workload.";
            } else {
                $recText .= "Suitable candidate with balanced load.";
            }

            return [
                'id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'designation' => $emp->designation ?? 'Technician',
                'active_assignments_count' => $count,
                'workload_status' => $count > 3 ? 'High' : ($count > 1 ? 'Medium' : 'Low'),
                'burnout_risk_score' => $burnout['score'],
                'burnout_risk_level' => $burnout['risk_level'],
                'comeback_ratio' => $comebackRatio,
                'routing_suitability_score' => $suitabilityScore,
                'recommendation' => $recText
            ];
        })->sortByDesc('routing_suitability_score')->values()->take(5)->toArray();

        // 2. Workshop Bay suggestions based on occupancy status
        $bays = DB::table('workshop_bays')
            ->where('tenant_id', $tenantId)
            ->get();

        $occupiedBays = DB::table('job_cards')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('workshop_bay_id')
            ->where('service_status', '!=', 'completed')
            ->pluck('workshop_bay_id')
            ->toArray();

        $baySuggestions = $bays->map(function ($bay) use ($occupiedBays) {
            $isOccupied = in_array($bay->id, $occupiedBays);
            return [
                'id' => $bay->id,
                'name' => $bay->name,
                'code' => $bay->code,
                'is_occupied' => $isOccupied,
                'recommendation' => $isOccupied ? 'Currently occupied (avoid auto-override)' : 'Available for scheduling'
            ];
        })->toArray();

        // 3. Recommended complaint templates
        // We look up historical common complaints or provide standard templates
        $commonComplaints = DB::table('job_cards')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('complaint')
            ->select('complaint', DB::raw('count(*) as count'))
            ->groupBy('complaint')
            ->orderBy('count', 'desc')
            ->take(5)
            ->pluck('complaint')
            ->toArray();

        if (count($commonComplaints) < 3) {
            $commonComplaints = array_merge($commonComplaints, [
                'Periodic oil replacement, filter cleaning, and basic engine tune-up.',
                'Squeaking noise from brakes. Inspect pad wear levels and disc turning.',
                'AC cabin air filter cleaning and coolant/gas pressure recharge.',
                'Suspension alignment inspection and strut bumper checking.',
                'Transmission gear shifting delay inspection and fluid level topping.'
            ]);
        }

        // 4. Parts suggestions based on vehicle parameters
        $suggestedParts = [];
        if ($vehicleId) {
            $vehicle = DB::table('vehicles')->where('id', $vehicleId)->first();
            if ($vehicle) {
                // Fetch parts that matches the vehicle make/brand
                $query = DB::table('parts')->where('tenant_id', $tenantId);
                
                $keywords = [$vehicle->make, $vehicle->model];
                $query->where(function ($q) use ($keywords) {
                    foreach ($keywords as $kw) {
                        if ($kw) {
                            $q->orWhere('name', 'like', "%{$kw}%")
                              ->orWhere('brand', 'like', "%{$kw}%");
                        }
                    }
                });
                
                $parts = $query->take(5)->get();
                $suggestedParts = $parts->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'brand' => $p->brand,
                        'sale_price' => $p->sale_price,
                        'stock_quantity' => $p->stock_quantity,
                        'recommendation' => 'Verify size and compatibility prior to invoice checkout.'
                    ];
                })->toArray();
            }
        }

        // If no vehicle parts were found or no vehicleId provided, return top stock parts
        if (empty($suggestedParts)) {
            $parts = DB::table('parts')
                ->where('tenant_id', $tenantId)
                ->orderBy('stock_quantity', 'desc')
                ->take(5)
                ->get();

            $suggestedParts = $parts->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'brand' => $p->brand,
                    'sale_price' => $p->sale_price,
                    'stock_quantity' => $p->stock_quantity,
                    'recommendation' => 'General top-stock inventory items.'
                ];
            })->toArray();
        }

        return [
            'mechanic_suggestions' => $mechanics,
            'workshop_bay_suggestions' => $baySuggestions,
            'complaint_templates' => $commonComplaints,
            'suggested_parts' => $suggestedParts,
            'notice' => 'Suggestions remain recommendation-only. All mechanic, bay, and inventory mutations require manual confirmation.'
        ];
    }
}
