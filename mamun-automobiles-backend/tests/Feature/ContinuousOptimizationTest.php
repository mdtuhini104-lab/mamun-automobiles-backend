<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\JobCard;
use App\Models\JobCardTask;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\AiRecommendation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContinuousOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $branch;
    protected $user;
    protected $customer;
    protected $vehicle;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup multi-tenant and branch structure
        $this->tenant = Tenant::create(['company_name' => 'Optimization Corp', 'domain' => 'opt.erp']);
        $this->branch = Branch::create(['tenant_id' => $this->tenant->id, 'name' => 'Dhaka West']);

        // 2. Setup user and assign Super Admin role
        $this->user = new User([
            'name' => 'Optimization Director',
            'email' => 'director@opt.erp',
            'password' => bcrypt('password123'),
        ]);
        $this->user->tenant_id = $this->tenant->id;
        $this->user->branch_id = $this->branch->id;
        $this->user->save();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
        $this->user->assignRole('Super Admin');

        // 3. Create customer
        $this->customer = new Customer([
            'name' => 'Opt Customer',
            'phone' => '01899887766',
            'email' => 'customer@opt.com',
            'address' => 'Dhaka, Bangladesh',
            'tag' => 'VIP'
        ]);
        $this->customer->tenant_id = $this->tenant->id;
        $this->customer->branch_id = $this->branch->id;
        $this->customer->save();

        // 4. Create vehicle
        $this->vehicle = Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2020,
            'license_plate' => 'DHK-11-2222',
        ]);

        // 5. Create employee/technician
        $dept = Department::create(['name' => 'Repair Ops']);
        $desg = Designation::create(['name' => 'Specialist', 'department_id' => $dept->id]);
        
        $this->employee = new Employee([
            'employee_code' => 'EMP-OPT-01',
            'first_name' => 'Optimus',
            'last_name' => 'Tech',
            'status' => 'active',
            'availability_status' => 'available'
        ]);
        $this->employee->tenant_id = $this->tenant->id;
        $this->employee->branch_id = $this->branch->id;
        $this->employee->user_id = $this->user->id;
        $this->employee->department_id = $dept->id;
        $this->employee->designation_id = $desg->id;
        $this->employee->save();

        // 6. Create workshop bay
        DB::table('workshop_bays')->insert([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bay 2',
            'code' => 'BAY-2',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function test_continuous_optimization_endpoint_structure()
    {
        $this->actingAs($this->user);

        // Preseed a job card and invoice to ensure there are no division-by-zero or empty errors
        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Brake pad replacement',
            'estimated_cost' => 300,
            'service_date' => now(),
            'service_status' => 'completed',
        ]);

        $response = $this->getJson('/api/v1/system/operations/continuous-optimization');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'rolling_kpi_comparisons' => [
                    'wow_trends' => [
                        'revenue_gross_change_pct',
                        'revenue_net_change_pct',
                        'quotation_conversion_change_pct',
                        'branch_efficiency_change_pct',
                        'technician_efficiency_change_pct',
                        'burnout_risk_change_pct',
                    ],
                    'mom_trends' => [
                        'revenue_gross_change_pct',
                        'revenue_net_change_pct',
                        'quotation_conversion_change_pct',
                        'branch_efficiency_change_pct',
                        'technician_efficiency_change_pct',
                        'burnout_risk_change_pct',
                    ]
                ],
                'operational_trend_analytics' => [
                    'completion_speed_improvement_pct',
                    'quotation_approval_acceleration_pct',
                    'payment_completion_acceleration_pct',
                    'congestion_reduction_pct',
                    'recommendation_usefulness_change_pct',
                    'supervisor_intervention_reduction_pct',
                    'idle_ratio_improvement_pct'
                ],
                'ai_refinement_telemetry' => [
                    'usefulness_degradation_detected',
                    'recommendation_drift_detected',
                    'trust_score_fluctuation_pct',
                    'false_positive_escalation_rate',
                    'routing_instability_score',
                    'burnout_prevention_effectiveness',
                    'drift_protection_active',
                    'dampening_factor'
                ],
                'workflow_bottlenecks' => [
                    'repeated_bay_congestion_detected',
                    'technician_overload_cycles_detected',
                    'stalled_quotations_count',
                    'delayed_approvals_count',
                    'repeated_supervisor_interventions_count',
                    'idle_technician_spikes_count',
                    'repeated_comeback_patterns_detected',
                    'suggestions'
                ],
                'revenue_and_trust_intelligence' => [
                    'unbilled_completed_jobs_count',
                    'unbilled_completed_jobs_leakage_amount',
                    'estimate_underbilling_leakage_amount',
                    'leakage_severity_level',
                    'customer_hesitation_score',
                    'quotation_abandonment_trend',
                    'comeback_trust_impact_score',
                    'delayed_payment_ratio'
                ]
            ]
        ]);
    }

    public function test_revenue_leakage_severity_classification()
    {
        $this->actingAs($this->user);

        // Preseed a completed job card with approved quotation total
        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Leakage check',
            'estimated_cost' => 1000,
            'service_date' => now(),
            'service_status' => 'completed',
        ]);

        // Scenario 1: Invoice has 10% underbilling leakage (Estimate is 1000, Invoice grand_total is 900. Leakage = 100 / 1000 = 10% -> MEDIUM)
        DB::table('invoices')->insert([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'job_card_id' => $jobCard->id,
            'invoice_number' => 'INV-LEAK-01',
            'grand_total' => 900.00,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson('/api/v1/system/operations/continuous-optimization');
        $response->assertStatus(200);
        $response->assertJsonPath('data.revenue_and_trust_intelligence.leakage_severity_level', 'MEDIUM');

        // Clean up invoices
        DB::table('invoices')->delete();

        // Scenario 2: Invoice has 20% underbilling leakage (Estimate is 1000, Invoice grand_total is 800. Leakage = 200 / 1000 = 20% -> HIGH)
        DB::table('invoices')->insert([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'job_card_id' => $jobCard->id,
            'invoice_number' => 'INV-LEAK-02',
            'grand_total' => 800.00,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson('/api/v1/system/operations/continuous-optimization');
        $response->assertStatus(200);
        $response->assertJsonPath('data.revenue_and_trust_intelligence.leakage_severity_level', 'HIGH');
    }

    public function test_recommendation_drift_protection_active_on_decline()
    {
        $this->actingAs($this->user);

        // Preseed a set of AI recommendations with declining usefulness over past 14 days
        // Day 1 to 14: Let's insert daily recommendations with declining effectiveness
        for ($i = 14; $i >= 0; $i--) {
            // Usefulness declining daily from 90 to 20
            $effectiveness = 90 - (14 - $i) * 5;
            DB::table('ai_recommendations')->insert([
                'recommendation_type' => 'workload_balancing',
                'source_id' => 1,
                'suggestion_data' => json_encode(['action' => 'bay_redistribution']),
                'explanation' => "Declining Rec #{$i}",
                'status' => 'approved',
                'confidence_score' => 95.0,
                'effectiveness_score' => $effectiveness,
                'outcome' => 'succeeded',
                'created_at' => Carbon::now()->subDays($i),
                'updated_at' => Carbon::now()->subDays($i)
            ]);
        }

        $response = $this->getJson('/api/v1/system/operations/continuous-optimization');
        $response->assertStatus(200);

        // Assert drift protection is activated
        $response->assertJsonPath('data.ai_refinement_telemetry.usefulness_degradation_detected', true);
        $response->assertJsonPath('data.ai_refinement_telemetry.drift_protection_active', true);
        // Dampening factor should be < 1.0 (e.g. 0.8)
        $this->assertLessThan(1.0, $response->json('data.ai_refinement_telemetry.dampening_factor'));
    }
}
