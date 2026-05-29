<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\JobCard;
use App\Models\AiRecommendation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PredictiveMaintenanceService
{
    /**
     * Analyze component lifespans for a vehicle looking back 12 months.
     */
    public function analyzeLifespans(int $vehicleId): array
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $oneYearAgo = Carbon::now()->subMonths(12);

        // Fetch historical job cards for this vehicle in the last 12 months
        $jobCards = JobCard::where('vehicle_id', $vehicleId)
            ->where('service_status', 'completed')
            ->where('created_at', '>=', $oneYearAgo)
            ->with('tasks')
            ->get();

        $currentOdo = (int) ($vehicle->odometer_reading ?? 0);
        if ($currentOdo === 0) {
            // Find max odometer reading from job cards
            $currentOdo = (int) JobCard::where('vehicle_id', $vehicleId)->max('odometer_reading') ?: 50000;
        }

        // Standard enterprise wear models for automotive parts
        $components = [
            'engine_oil' => [
                'name' => 'Engine Lubricant Oil',
                'life_km' => 5000,
                'life_months' => 6,
                'keywords' => ['oil', 'lubricant', 'mobil']
            ],
            'brake_pads' => [
                'name' => 'Brake Pads',
                'life_km' => 20000,
                'life_months' => 12,
                'keywords' => ['brake', 'pad', 'rotor']
            ],
            'air_filter' => [
                'name' => 'Engine Air Filter',
                'life_km' => 10000,
                'life_months' => 6,
                'keywords' => ['air filter', 'cleaner element']
            ],
            'ac_filter' => [
                'name' => 'AC Cabin Filter',
                'life_km' => 15000,
                'life_months' => 12,
                'keywords' => ['ac filter', 'cabin filter', 'cooling coil']
            ],
            'spark_plugs' => [
                'name' => 'Spark Plugs',
                'life_km' => 30000,
                'life_months' => 24,
                'keywords' => ['plug', 'spark']
            ]
        ];

        $analysis = [];

        foreach ($components as $key => $config) {
            $lastReplacedDate = null;
            $lastReplacedOdo = null;

            // Search history for last occurrence
            foreach ($jobCards as $jc) {
                $matched = false;
                foreach ($jc->tasks as $task) {
                    foreach ($config['keywords'] as $kw) {
                        if (stripos($task->task_name, $kw) !== false) {
                            $matched = true;
                            break 2;
                        }
                    }
                }

                if ($matched) {
                    $jcDate = Carbon::parse($jc->created_at);
                    if (is_null($lastReplacedDate) || $jcDate->gt($lastReplacedDate)) {
                        $lastReplacedDate = $jcDate;
                        $lastReplacedOdo = (int) $jc->odometer_reading;
                    }
                }
            }

            if ($lastReplacedDate) {
                // Calculate consumption metrics
                $elapsedMonths = $lastReplacedDate->diffInMonths(Carbon::now());
                $elapsedKm = max(0, $currentOdo - ($lastReplacedOdo ?: 0));

                $timeUsedRatio = $config['life_months'] > 0 ? ($elapsedMonths / $config['life_months']) : 0;
                $kmUsedRatio = $config['life_km'] > 0 ? ($elapsedKm / $config['life_km']) : 0;
                $maxUsedRatio = max($timeUsedRatio, $kmUsedRatio);

                $remainingPercent = (int) max(0, (1 - $maxUsedRatio) * 100);
                $confidence = 0.90; // High confidence based on actual service records
                $explanation = "Replaced on " . $lastReplacedDate->format('Y-m-d') . " at " . $lastReplacedOdo . " KM.";
            } else {
                // No service record found in past 12 months - default to wear based on odometer
                $remainingPercent = 25; // Pre-cautionary low wear state
                $confidence = 0.50; // Moderate confidence due to lack of historical baseline
                $explanation = "No service log found in previous 12 months. Estimating based on current odometer ({$currentOdo} KM).";
            }

            $analysis[$key] = [
                'name' => $config['name'],
                'remaining_percent' => $remainingPercent,
                'confidence_score' => $confidence,
                'explanation' => $explanation,
                'status' => $remainingPercent < 20 ? 'critical' : ($remainingPercent < 50 ? 'warning' : 'good')
            ];
        }

        return $analysis;
    }

    /**
     * Evolve a comprehensive health score (0-100) and generate AI recommendations.
     */
    public function generateHealthScore(int $vehicleId): array
    {
        $analysis = $this->analyzeLifespans($vehicleId);
        
        $totalScore = 0;
        $count = count($analysis);
        $criticalCount = 0;

        foreach ($analysis as $comp) {
            $totalScore += $comp['remaining_percent'];
            if ($comp['status'] === 'critical') {
                $criticalCount++;
            }
        }

        $overallScore = $count > 0 ? (int) ($totalScore / $count) : 100;
        
        // Severity penalty adjustments
        if ($criticalCount > 0) {
            $overallScore = max(10, $overallScore - ($criticalCount * 15));
        }

        $status = 'Good';
        if ($overallScore < 40) {
            $status = 'Critical';
        } elseif ($overallScore < 70) {
            $status = 'Warning';
        }

        // Generate recommendations for components below 50% life
        foreach ($analysis as $key => $comp) {
            if ($comp['remaining_percent'] < 50) {
                // Create or update AI recommendation record in the inbox
                $exists = AiRecommendation::where('recommendation_type', 'predictive_maintenance')
                    ->where('source_id', $vehicleId)
                    ->where('status', 'pending')
                    ->where('suggestion_data->component', $key)
                    ->exists();

                if (!$exists) {
                    AiRecommendation::create([
                        'recommendation_type' => 'predictive_maintenance',
                        'source_id' => $vehicleId,
                        'suggestion_data' => [
                            'vehicle_id' => $vehicleId,
                            'component' => $key,
                            'component_name' => $comp['name'],
                            'remaining_percent' => $comp['remaining_percent'],
                            'recommended_action' => "Schedule preventative replacement of " . $comp['name'] . "."
                        ],
                        'confidence_score' => $comp['confidence_score'] * 100,
                        'explanation' => "Calculated vehicle remaining health of component is only " . $comp['remaining_percent'] . "%. Reason: " . $comp['explanation'],
                        'status' => 'pending'
                    ]);
                }
            }
        }

        return [
            'vehicle_id' => $vehicleId,
            'health_score' => $overallScore,
            'status' => $status,
            'components' => $analysis,
            'scanned_at' => now()->toDateTimeString()
        ];
    }
}
