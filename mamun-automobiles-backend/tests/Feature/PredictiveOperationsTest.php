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
use App\Models\SystemHealthAlert;
use App\Models\PredictiveSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PredictiveOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $branch;
    protected $user;
    protected $department;
    protected $designation;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup multi-tenant and branch structure
        $this->tenant = Tenant::create(['company_name' => 'Predictive Corp', 'domain' => 'predictive.erp']);
        $this->branch = Branch::create(['tenant_id' => $this->tenant->id, 'name' => 'Dhaka Main']);

        // 2. Setup user and assign Super Admin role
        $this->user = new User([
            'name' => 'Operations Admin',
            'email' => 'ops@predictive.erp',
            'password' => bcrypt('password123'),
        ]);
        $this->user->tenant_id = $this->tenant->id;
        $this->user->branch_id = $this->branch->id;
        $this->user->save();

        // Explicitly create Super Admin role across all guards to prevent RoleDoesNotExist exceptions
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
        $this->user->assignRole('Super Admin');

        // 3. Setup department, designation and employee
        $this->department = Department::create(['name' => 'Mechanical']);
        $this->designation = Designation::create(['name' => 'Lead Technician', 'department_id' => $this->department->id]);
        
        $this->employee = new Employee([
            'employee_code' => 'EMP-OPS-01',
            'first_name' => 'Abul',
            'last_name' => 'Mechanic',
            'status' => 'active',
            'availability_status' => 'available'
        ]);
        $this->employee->tenant_id = $this->tenant->id;
        $this->employee->branch_id = $this->branch->id;
        $this->employee->user_id = $this->user->id;
        $this->employee->department_id = $this->department->id;
        $this->employee->designation_id = $this->designation->id;
        $this->employee->save();

        // 4. Create workshop bay
        DB::table('workshop_bays')->insert([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bay A',
            'code' => 'BAY-A',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function test_get_predictive_metrics_endpoint()
    {
        $this->actingAs($this->user);

        // Assert empty initial states calculate correctly
        $response = $this->getJson('/api/v1/system/operations/predictive-metrics');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'flow_efficiency_score',
                'segmentation' => [
                    'branches',
                    'departments',
                    'technicians',
                    'bays',
                ],
                'technician_burnout_detection',
                'throughput',
                'predictive_alerts'
            ]
        ]);

        // Verify snapshot was created in predictive_snapshots table
        $this->assertTrue(PredictiveSnapshot::where('tenant_id', $this->tenant->id)->exists());
    }

    public function test_auto_escalation_rules_and_cooldown()
    {
        $this->actingAs($this->user);

        // 1. Create a stalled task (updated_at > 30 minutes ago, in_progress status)
        $customer = \App\Models\Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Test Customer',
            'phone' => '01700000000',
            'email' => 'test@predictive.com'
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
            'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
        ]);

        $task = JobCardTask::create([
            'job_card_id' => $jobCard->id,
            'name' => 'AC Gas Refill',
            'status' => 'in_progress',
            'estimated_minutes' => 30,
            'actual_minutes' => 0,
        ]);

        // Force update updated_at timestamp to 35 minutes ago to simulate a stalled task
        DB::table('job_card_tasks')->where('id', $task->id)->update([
            'updated_at' => Carbon::now()->subMinutes(35)
        ]);

        // 2. Create a delayed quotation sent 2.5 hours ago
        $quote = Quotation::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'job_card_id' => $jobCard->id,
            'quotation_number' => 'QT-' . uniqid(),
            'status' => 'sent',
            'total_amount' => 5000,
        ]);

        DB::table('quotations')->where('id', $quote->id)->update([
            'updated_at' => Carbon::now()->subMinutes(150)
        ]);

        // 3. Trigger auto-escalations endpoint
        $response = $this->postJson('/api/v1/system/operations/trigger-escalations');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
        ]);

        // Should have raised stalled task alert (P4) and delayed approval alert (P2)
        $this->assertTrue(SystemHealthAlert::where('alert_type', 'P4_minor_congestion')->exists());
        $this->assertTrue(SystemHealthAlert::where('alert_type', 'P2_delayed_approval')->exists());

        // Save current count of alerts
        $firstScanAlertsCount = SystemHealthAlert::count();

        // 4. Run scan again immediately - cooldown protection should kick in and NOT create duplicate alerts
        $response2 = $this->postJson('/api/v1/system/operations/trigger-escalations');
        $response2->assertStatus(200);
        $response2->assertJsonFragment([
            'new_alerts_count' => 0
        ]);

        $this->assertEquals($firstScanAlertsCount, SystemHealthAlert::count());
    }

    public function test_auto_escalation_safety_controls()
    {
        $this->actingAs($this->user);

        $customer = \App\Models\Customer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Safety Customer',
            'phone' => '01711111111',
            'email' => 'safety@predictive.com'
        ]);

        $vehicle = \App\Models\Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2019,
            'license_plate' => 'DHK-88-9999',
        ]);

        // 1. Create a completed task updated 40 mins ago -> Should NOT escalate
        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Check Brakes',
            'estimated_cost' => 1000,
            'service_date' => now(),
            'service_status' => \App\Enums\ServiceStatus::COMPLETED,
        ]);

        $task = JobCardTask::create([
            'job_card_id' => $jobCard->id,
            'name' => 'Pad replacement',
            'status' => 'completed',
            'estimated_minutes' => 20,
            'actual_minutes' => 25,
        ]);

        DB::table('job_card_tasks')->where('id', $task->id)->update([
            'updated_at' => Carbon::now()->subMinutes(40)
        ]);

        // 2. Create a customer-paused job card occupying a bay -> Should NOT escalate prolonged bay occupation
        $bay = WorkshopBay::where('tenant_id', $this->tenant->id)->first();
        $pausedJobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'workshop_bay_id' => $bay->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Suspension check',
            'estimated_cost' => 2000,
            'service_date' => now(),
            'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
        ]);

        $pausedQuote = Quotation::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'job_card_id' => $pausedJobCard->id,
            'quotation_number' => 'QT-' . uniqid(),
            'status' => 'sent',
            'total_amount' => 5000,
        ]);

        \App\Models\WorkOrder::create([
            'tenant_id' => $this->tenant->id,
            'job_card_id' => $pausedJobCard->id,
            'quotation_id' => $pausedQuote->id,
            'work_order_number' => 'WO-' . uniqid(),
            'status' => 'paused',
        ]);

        DB::table('job_cards')->where('id', $pausedJobCard->id)->update([
            'updated_at' => Carbon::now()->subHours(5)
        ]);

        // Run auto-escalations scan
        $response = $this->postJson('/api/v1/system/operations/trigger-escalations');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'new_alerts_count' => 0
        ]);

        // Verify no alerts were created
        $this->assertEquals(0, SystemHealthAlert::count());
    }

    public function test_workflow_suggestions_intelligent_routing()
    {
        $this->actingAs($this->user);

        // Verify that suggestion payload contains Routing Suitability Score and Burnout metrics
        $response = $this->getJson('/api/v1/workshop/suggestions');
        $response->assertStatus(200);
        $mechanics = $response->json('data.mechanic_suggestions');

        $this->assertNotEmpty($mechanics);
        $this->assertArrayHasKey('burnout_risk_score', $mechanics[0]);
        $this->assertArrayHasKey('routing_suitability_score', $mechanics[0]);
        $this->assertArrayHasKey('comeback_ratio', $mechanics[0]);
    }
}
