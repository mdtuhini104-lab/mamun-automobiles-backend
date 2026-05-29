<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\WorkshopBay;
use App\Models\JobCard;
use App\Models\JobCardTask;
use App\Models\JobTaskAssignment;
use App\Models\Quotation;
use App\Models\AiRecommendation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdaptiveIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $branch;
    protected $user;
    protected $department;
    protected $designation;
    protected $employee1;
    protected $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup multi-tenant and branch structure
        $this->tenant = Tenant::create(['company_name' => 'Adaptive Corp', 'domain' => 'adaptive.erp']);
        $this->branch = Branch::create(['tenant_id' => $this->tenant->id, 'name' => 'Dhaka Main']);

        // 2. Setup user and assign Super Admin role
        $this->user = new User([
            'name' => 'Operations Admin',
            'email' => 'ops@adaptive.erp',
            'password' => bcrypt('password123'),
        ]);
        $this->user->tenant_id = $this->tenant->id;
        $this->user->branch_id = $this->branch->id;
        $this->user->save();

        // Explicitly create Super Admin role across all guards
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
        $this->user->assignRole('Super Admin');

        // 3. Setup department and designation
        $this->department = Department::create(['name' => 'Mechanical']);
        $this->designation = Designation::create(['name' => 'Lead Technician', 'department_id' => $this->department->id]);
        
        // 4. Create two employees
        $this->employee1 = new Employee([
            'employee_code' => 'EMP-OPS-01',
            'first_name' => 'Abul',
            'last_name' => 'Mechanic',
            'status' => 'active',
            'availability_status' => 'available'
        ]);
        $this->employee1->tenant_id = $this->tenant->id;
        $this->employee1->branch_id = $this->branch->id;
        $this->employee1->user_id = $this->user->id;
        $this->employee1->department_id = $this->department->id;
        $this->employee1->designation_id = $this->designation->id;
        $this->employee1->save();

        $user2 = new User([
            'name' => 'Second Worker',
            'email' => 'tech2@adaptive.erp',
            'password' => bcrypt('password123'),
        ]);
        $user2->tenant_id = $this->tenant->id;
        $user2->branch_id = $this->branch->id;
        $user2->save();

        $this->employee2 = new Employee([
            'employee_code' => 'EMP-OPS-02',
            'first_name' => 'Babul',
            'last_name' => 'Mechanic',
            'status' => 'active',
            'availability_status' => 'available'
        ]);
        $this->employee2->tenant_id = $this->tenant->id;
        $this->employee2->branch_id = $this->branch->id;
        $this->employee2->user_id = $user2->id;
        $this->employee2->department_id = $this->department->id;
        $this->employee2->designation_id = $this->designation->id;
        $this->employee2->save();

        // 5. Create two workshop bays (Bay A: Congested, Bay B: Idle)
        DB::table('workshop_bays')->insert([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bay A',
            'code' => 'BAY-A',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('workshop_bays')->insert([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bay B',
            'code' => 'BAY-B',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function test_get_adaptive_analytics_endpoint()
    {
        $this->actingAs($this->user);

        // 1. Create a completed task to verify prediction accuracy math
        $customer = \App\Models\Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Test Customer',
            'phone' => '01700000000',
            'email' => 'test@adaptive.com'
        ]);

        $vehicle = \App\Models\Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'make' => 'Toyota',
            'model' => 'Allion',
            'year' => 2018,
            'license_plate' => 'DHK-77-8888',
        ]);

        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'AC issues',
            'estimated_cost' => 1200,
            'service_date' => now(),
            'service_status' => \App\Enums\ServiceStatus::COMPLETED,
        ]);

        JobCardTask::create([
            'job_card_id' => $jobCard->id,
            'name' => 'AC Gas Refill',
            'status' => 'completed',
            'estimated_minutes' => 30,
            'actual_minutes' => 25, // 83.3% accuracy
        ]);

        $response = $this->getJson('/api/v1/system/operations/adaptive-analytics');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'operational_trust_score',
                'prediction_accuracy' => [
                    'prediction_accuracy_rate',
                    'escalation_correctness',
                    'congestion_prediction_reliability',
                    'routing_effectiveness',
                    'operational_delay_forecast_accuracy'
                ],
                'escalation_responsiveness' => [
                    'avg_response_time_minutes',
                    'ignored_escalation_ratio',
                    'repeated_escalation_chains_count'
                ],
                'workforce_sustainability',
                'adaptive_threshold_baselines' => [
                    'normal_workflow_duration_mins',
                    'branch_throughput_daily_avg'
                ]
            ]
        ]);

        // Assert trust score exists in payload
        $this->assertNotNull($response->json('data.operational_trust_score'));
    }

    public function test_get_load_balancing_recommendations_and_spam_protection()
    {
        $this->actingAs($this->user);

        // Setup 4 active job cards in Bay A to trigger congestion rebalancing recommendations
        $customer = \App\Models\Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Load Customer',
            'phone' => '01712345678',
            'email' => 'load@adaptive.com'
        ]);

        $vehicle = \App\Models\Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'make' => 'Toyota',
            'model' => 'Premio',
            'year' => 2019,
            'license_plate' => 'DHK-11-2222',
        ]);

        $bayA = DB::table('workshop_bays')->where('tenant_id', $this->tenant->id)->where('name', 'Bay A')->first();

        // Create 4 active job cards in Bay A
        for ($i = 0; $i < 4; $i++) {
            $jc = new JobCard([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'assigned_mechanic_id' => $this->user->id,
                'complaint' => 'Oil leak ' . $i,
                'estimated_cost' => 1000,
                'service_date' => now(),
                'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
            ]);
            $jc->tenant_id = $this->tenant->id;
            $jc->workshop_bay_id = $bayA->id;
            $jc->save();
        }

        // 1. Fetch recommendations - should generate 1 new bay redistribution suggestion
        $response = $this->getJson('/api/v1/system/operations/load-balancing');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'type' => 'bay_redistribution',
            'confidence_score' => 87.0
        ]);

        // Confirm database has stored this recommendation
        $this->assertEquals(1, AiRecommendation::where('recommendation_type', 'workload_balancing')->count());

        // 2. Fetch again immediately - Anti-Spam protection should trigger, preventing duplicates
        $response2 = $this->getJson('/api/v1/system/operations/load-balancing');
        $response2->assertStatus(200);
        $response2->assertJsonCount(0, 'data'); // 0 because the recommendation is already stored and on cooldown
    }

    public function test_get_anomaly_detections_classified_by_severity()
    {
        $this->actingAs($this->user);

        // Create a stalled task (Low) and delayed quotation approval (High)
        $customer = \App\Models\Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Anomaly Customer',
            'phone' => '01799999999',
            'email' => 'anomaly@adaptive.com'
        ]);

        $vehicle = \App\Models\Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'make' => 'Mazda',
            'model' => 'Axela',
            'year' => 2017,
            'license_plate' => 'DHK-22-3333',
        ]);

        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'AC issue',
            'estimated_cost' => 1500,
            'service_date' => now(),
            'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
        ]);

        $task = JobCardTask::create([
            'job_card_id' => $jobCard->id,
            'name' => 'AC Valve Clean',
            'status' => 'in_progress',
            'estimated_minutes' => 30,
            'actual_minutes' => 0,
        ]);

        DB::table('job_card_tasks')->where('id', $task->id)->update([
            'updated_at' => Carbon::now()->subMinutes(20)
        ]);

        $quote = Quotation::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'job_card_id' => $jobCard->id,
            'quotation_number' => 'QT-' . uniqid(),
            'status' => 'sent',
            'total_amount' => 3000,
        ]);

        DB::table('quotations')->where('id', $quote->id)->update([
            'updated_at' => Carbon::now()->subMinutes(130)
        ]);

        // Query anomaly detections
        $response = $this->getJson('/api/v1/system/operations/anomalies');
        $response->assertStatus(200);

        // Should return a "Low" severity stalled task and a "High" severity delayed approval
        $response->assertJsonFragment([
            'severity' => 'Low',
            'title' => 'Temporary Delay Warning'
        ]);
        $response->assertJsonFragment([
            'severity' => 'High',
            'title' => 'Escalation Overdue Approval'
        ]);
    }

    public function test_get_load_balancing_with_burnout_recovery_recommendations()
    {
        $this->actingAs($this->user);

        // 1. Create attendance records indicating high overtime for employee1
        for ($i = 1; $i <= 3; $i++) {
            DB::table('attendances')->insert([
                'user_id' => $this->employee1->user_id,
                'date' => Carbon::now()->subDays($i)->format('Y-m-d'),
                'check_in' => '08:00:00',
                'check_out' => '20:00:00', // 12 hours (4 hours overtime)
                'status' => 'present',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 2. Create active tasks assigned to employee1
        $customer = \App\Models\Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Burnout Cust',
            'phone' => '01711122233',
            'email' => 'burnout@cust.com'
        ]);

        $vehicle = \App\Models\Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2019,
            'license_plate' => 'DHK-33-4444',
        ]);

        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Suspension noise',
            'estimated_cost' => 2000,
            'service_date' => now(),
            'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
        ]);

        $task1 = JobCardTask::create([
            'job_card_id' => $jobCard->id,
            'name' => 'Strut Replacement',
            'status' => 'in_progress',
            'estimated_minutes' => 60,
            'actual_minutes' => 0,
        ]);

        $task2 = JobCardTask::create([
            'job_card_id' => $jobCard->id,
            'name' => 'Bush Replacement',
            'status' => 'in_progress',
            'estimated_minutes' => 45,
            'actual_minutes' => 0,
        ]);

        DB::table('job_task_assignments')->insert([
            'job_card_task_id' => $task1->id,
            'employee_id' => $this->employee1->id,
            'status' => 'active',
            'allocated_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('job_task_assignments')->insert([
            'job_card_task_id' => $task2->id,
            'employee_id' => $this->employee1->id,
            'status' => 'active',
            'allocated_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 3. Request load balancing suggestions
        $response = $this->getJson('/api/v1/system/operations/load-balancing');
        $response->assertStatus(200);

        // Should return a burnout recovery recommendation for employee1
        $response->assertJsonFragment([
            'type' => 'burnout_recovery',
            'confidence_score' => 95.0
        ]);

        $response->assertJsonFragment([
            'operational_confidence_reasoning' => [
                'technician overload risk',
                'burnout risk increased',
                'recovery period recommended'
            ]
        ]);

        // Assert details suggest correct structures
        $data = $response->json('data');
        $burnoutRec = collect($data)->where('type', 'burnout_recovery')->first();
        $this->assertNotNull($burnoutRec);
        $this->assertEquals('burnout_recovery_protection', $burnoutRec['details']['action']);
        $this->assertEquals('24 hours rest recommended', $burnoutRec['details']['recovery_period']);
        $this->assertEquals('24 hours', $burnoutRec['details']['temporary_cooldown_period']);
    }
}
