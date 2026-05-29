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
use App\Models\JobCard;
use App\Models\JobCardTask;
use App\Models\AiRecommendation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SemiAutonomousIntelligenceTest extends TestCase
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
        $this->tenant = Tenant::create(['company_name' => 'SemiAuto Corp', 'domain' => 'semiauto.erp']);
        $this->branch = Branch::create(['tenant_id' => $this->tenant->id, 'name' => 'Dhaka Main']);

        // 2. Setup user and assign Super Admin role
        $this->user = new User([
            'name' => 'Operations Admin',
            'email' => 'ops@semiauto.erp',
            'password' => bcrypt('password123'),
        ]);
        $this->user->tenant_id = $this->tenant->id;
        $this->user->branch_id = $this->branch->id;
        $this->user->save();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
        $this->user->assignRole('Super Admin');

        // 3. Setup department and designation
        $this->department = Department::create(['name' => 'Mechanical']);
        $this->designation = Designation::create(['name' => 'Lead Technician', 'department_id' => $this->department->id]);
        
        // 4. Create two employees
        $this->employee1 = new Employee([
            'employee_code' => 'EMP-SEM-01',
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
            'email' => 'tech2@semiauto.erp',
            'password' => bcrypt('password123'),
        ]);
        $user2->tenant_id = $this->tenant->id;
        $user2->branch_id = $this->branch->id;
        $user2->save();

        $this->employee2 = new Employee([
            'employee_code' => 'EMP-SEM-02',
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

        // 5. Create workshop bays
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

    public function test_action_recommendation_override()
    {
        $this->actingAs($this->user);

        // 1. Create a recommendation
        $rec = AiRecommendation::create([
            'recommendation_type' => 'workload_balancing',
            'source_id' => 1,
            'suggestion_data' => ['action' => 'bay_redistribution'],
            'confidence_score' => 87.0,
            'explanation' => 'Test workload balancing explanation',
            'status' => 'pending'
        ]);

        // 2. Override: Approve recommendation
        $response = $this->postJson("/api/v1/system/operations/recommendations/{$rec->id}/action", [
            'status' => 'approved',
            'outcome' => 'succeeded',
            'effectiveness_score' => 95.0,
            'feedback_notes' => 'Highly accurate and assisted recovery.'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', 'approved');
        $response->assertJsonPath('data.outcome', 'succeeded');
        $response->assertJsonPath('data.effectiveness_score', 95);
        $response->assertJsonPath('data.suggestion_data.feedback_notes', 'Highly accurate and assisted recovery.');

        // Verify DB values directly
        $dbRec = AiRecommendation::find($rec->id);
        $this->assertEquals('approved', $dbRec->status);
        $this->assertEquals('succeeded', $dbRec->outcome);
        $this->assertEquals(95.0, $dbRec->effectiveness_score);
        $this->assertEquals(1, $dbRec->actioned_by_id);

        // Verify audit log has recorded the override action
        $this->assertTrue(DB::table('audit_logs')
            ->where('action', 'human_override_recommendation')
            ->where('user_id', $this->user->id)
            ->exists());
    }

    public function test_coordination_simulation_remains_readonly()
    {
        $this->actingAs($this->user);

        $bayA = DB::table('workshop_bays')->where('tenant_id', $this->tenant->id)->where('name', 'Bay A')->first();
        $bayB = DB::table('workshop_bays')->where('tenant_id', $tenantId = $this->tenant->id)->where('name', 'Bay B')->first();

        // 1. Request a bay redistribution simulation
        $response = $this->postJson('/api/v1/system/operations/simulate', [
            'action_type' => 'bay_redistribution',
            'source_id' => $bayA->id,
            'target_id' => $bayB->id
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'projection_type',
            'simulated_at',
            'projection' => [
                'source_bay_utilization_before',
                'source_bay_utilization_after',
                'target_bay_utilization_before',
                'target_bay_utilization_after',
                'projected_daily_throughput_increase',
                'estimated_hours_to_congestion_recovery',
                'projected_flow_efficiency_score_change',
                'explanation'
            ]
        ]);

        // Assert database state was not modified (readonly / sandboxed check)
        $this->assertEquals(0, DB::table('ai_recommendations')->count());
        $this->assertEquals(0, DB::table('system_health_alerts')->count());
    }

    public function test_self_tuning_baselines_with_rolling_averages()
    {
        $this->actingAs($this->user);

        // Pre-seed completed tasks to simulate a longer historical duration
        $customer = \App\Models\Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Sim Customer',
            'phone' => '01711223344',
            'email' => 'sim@cust.com'
        ]);

        $vehicle = \App\Models\Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'make' => 'Nissan',
            'model' => 'Sunny',
            'year' => 2015,
            'license_plate' => 'DHK-44-5555',
        ]);

        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Suspension check',
            'estimated_cost' => 1000,
            'service_date' => now(),
            'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
        ]);

        // Insert completed tasks with high average duration to calibrate baseline
        for ($i = 0; $i < 5; $i++) {
            $taskObj = new JobCardTask([
                'job_card_id' => $jobCard->id,
                'name' => 'Strut Repair ' . $i,
                'status' => 'completed',
                'estimated_minutes' => 180,
            ]);
            $taskObj->actual_minutes = 200; // bypass fillable
            $taskObj->save();
        }

        // Add a stalled task
        $task = JobCardTask::create([
            'job_card_id' => $jobCard->id,
            'name' => 'Wheel Alignment',
            'status' => 'in_progress',
            'estimated_minutes' => 30,
            'actual_minutes' => 0,
        ]);

        // Set last updated at to 25 minutes ago
        DB::table('job_card_tasks')->where('id', $task->id)->update([
            'updated_at' => Carbon::now()->subMinutes(25)
        ]);

        // Trigger escalations scan
        // Under default 30-min stall limit, this task wouldn't escalate.
        // Under self-tuned baseline, 20% of 200 mins average is 40 minutes, so it shouldn't escalate either.
        $response = $this->postJson('/api/v1/system/operations/trigger-escalations');
        $response->assertStatus(200);

        // Assert no new alerts triggered because tuned threshold is 40 minutes (25 < 40)
        $this->assertEquals(0, DB::table('system_health_alerts')->count());

        // Update task to 45 minutes ago
        DB::table('job_card_tasks')->where('id', $task->id)->update([
            'updated_at' => Carbon::now()->subMinutes(45)
        ]);

        // Run escalations scan again
        // Now it exceeds the self-tuned 40-min threshold (45 > 40), so it should escalate
        $response2 = $this->postJson('/api/v1/system/operations/trigger-escalations');
        $response2->assertStatus(200);
        $this->assertEquals(1, DB::table('system_health_alerts')->count());
        
        // Verify custom threshold is recorded inside the alert metrics
        $this->assertTrue(DB::table('system_health_alerts')
            ->where('alert_type', 'P4_minor_congestion')
            ->whereRaw("json_extract(metrics, '$.tuned_threshold') = 40")
            ->exists());
    }

    public function test_learning_metrics_and_reinforcement_calibration()
    {
        $this->actingAs($this->user);

        // Pre-seed historical recommendations to calibrate scoring
        // pre-seed 2 approved workload balancing recommendations with poor effectiveness (e.g. 50%)
        DB::table('ai_recommendations')->insert([
            'recommendation_type' => 'workload_balancing',
            'source_id' => 1,
            'suggestion_data' => json_encode(['action' => 'bay_redistribution']),
            'confidence_score' => 87.0,
            'explanation' => 'Balances congested Bay',
            'status' => 'approved',
            'outcome' => 'succeeded',
            'effectiveness_score' => 50.0, // avg = 50.0%, multiplier = 0.50, capped at floor 0.70
            'created_at' => Carbon::now()->subMinutes(40),
            'updated_at' => Carbon::now()->subMinutes(40),
        ]);

        // Get learning metrics
        $response = $this->getJson('/api/v1/system/operations/learning-metrics');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_recommendations',
                'accepted_count',
                'rejected_count',
                'acceptance_rate',
                'burnout_recovery_success_rate',
                'ignored_alert_ratio',
                'routing_effectiveness_rate',
                'congestion_reduction_effectiveness',
                'calibrated_confidence_multipliers' => [
                    'workload_balancing',
                    'technician_allocation'
                ]
            ]
        ]);

        // Assert dynamic confidence multiplier caps applied (floor limit = 0.70)
        $this->assertEquals(0.70, $response->json('data.calibrated_confidence_multipliers.workload_balancing'));

        // Verify that a future load-balancing call uses the calibrated multiplier
        // Setup a congested Bay A and idle Bay B to trigger recommendation
        $bayA = DB::table('workshop_bays')->where('tenant_id', $this->tenant->id)->where('name', 'Bay A')->first();
        
        $customer = \App\Models\Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Load Customer',
            'phone' => '01712345678',
            'email' => 'load@semiauto.com'
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

        // Call load balancing. Base confidence 87.0 * 0.70 multiplier = 60.9%
        $response2 = $this->getJson('/api/v1/system/operations/load-balancing');
        $response2->assertStatus(200);

        $response2->assertJsonCount(1, 'data');
        $response2->assertJsonFragment([
            'type' => 'bay_redistribution',
            'confidence_score' => 60.9
        ]);
    }
}
