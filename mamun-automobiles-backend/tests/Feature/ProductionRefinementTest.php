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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProductionRefinementTest extends TestCase
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
        $this->tenant = Tenant::create(['company_name' => 'Refined Auto Corp', 'domain' => 'refined.erp']);
        $this->branch = Branch::create(['tenant_id' => $this->tenant->id, 'name' => 'Dhaka North']);

        // 2. Setup user and assign Super Admin role
        $this->user = new User([
            'name' => 'Operations Manager',
            'email' => 'manager@refined.erp',
            'password' => bcrypt('password123'),
        ]);
        $this->user->tenant_id = $this->tenant->id;
        $this->user->branch_id = $this->branch->id;
        $this->user->save();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
        $this->user->assignRole('Super Admin');

        // 3. Create customer (with VIP tag)
        $this->customer = new Customer([
            'name' => 'Mamun Customer',
            'phone' => '01799887766',
            'email' => 'customer@mamun.com',
            'address' => 'Dhaka, Bangladesh',
            'tag' => 'VIP',
            'notes' => 'Corporate partner'
        ]);
        $this->customer->tenant_id = $this->tenant->id;
        $this->customer->branch_id = $this->branch->id;
        $this->customer->save();

        // 4. Create vehicle
        $this->vehicle = Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'make' => 'Toyota',
            'model' => 'Axio',
            'year' => 2017,
            'license_plate' => 'DHK-11-2222',
        ]);

        // 5. Create employee/technician
        $dept = Department::create(['name' => 'Refining Department']);
        $desg = Designation::create(['name' => 'Senior Welder', 'department_id' => $dept->id]);
        
        $this->employee = new Employee([
            'employee_code' => 'EMP-REF-01',
            'first_name' => 'Kamal',
            'last_name' => 'Mechanic',
            'status' => 'active',
            'availability_status' => 'available'
        ]);
        $this->employee->tenant_id = $this->tenant->id;
        $this->employee->branch_id = $this->branch->id;
        $this->employee->user_id = $this->user->id;
        $this->employee->department_id = $dept->id;
        $this->employee->designation_id = $desg->id;
        $this->employee->save();
    }

    public function test_customer_quick_load_caching()
    {
        $this->actingAs($this->user);

        // 1. Initial hit (cache miss)
        $response1 = $this->getJson("/api/v1/system/operations/customer-quick-load?customer_id={$this->customer->id}");
        $response1->assertStatus(200);
        $response1->assertJsonPath('cache_hit', false);
        $response1->assertJsonStructure([
            'success',
            'cache_hit',
            'data' => [
                'customer',
                'vehicles',
                'past_invoices_count',
                'recent_quotations',
                'pricing_policy',
                'negotiated_pricing_structure'
            ]
        ]);

        // Verify tier discount (VIP) is mapped
        $response1->assertJsonPath('data.pricing_policy', 'tier_classification_fallback');
        $response1->assertJsonPath('data.negotiated_pricing_structure.0.pricing_tier', 'vip');
        $response1->assertJsonPath('data.negotiated_pricing_structure.0.labor_discount_multiplier', 0.95);

        // 2. Second hit (cache hit)
        $response2 = $this->getJson("/api/v1/system/operations/customer-quick-load?customer_id={$this->customer->id}");
        $response2->assertStatus(200);
        $response2->assertJsonPath('cache_hit', true);

        // 3. Cache Invalidation on profile update
        $this->customer->update(['name' => 'Mamun Customer Revised']);
        
        $response3 = $this->getJson("/api/v1/system/operations/customer-quick-load?customer_id={$this->customer->id}");
        $response3->assertStatus(200);
        $response3->assertJsonPath('cache_hit', false); // Missed because cache was invalidated
    }

    public function test_customer_quick_load_with_negotiated_pricing()
    {
        $this->actingAs($this->user);

        // Preseed customer pricings rule
        DB::table('customer_pricings')->insert([
            'customer_id' => $this->customer->id,
            'labor_service_name' => 'Engine Tune Up',
            'custom_labor_rate' => 850.00,
            'effective_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson("/api/v1/system/operations/customer-quick-load?customer_id={$this->customer->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.pricing_policy', 'custom_negotiated_pricing');
        $response->assertJsonPath('data.negotiated_pricing_structure.0.labor_service_name', 'Engine Tune Up');
        $response->assertJsonPath('data.negotiated_pricing_structure.0.custom_labor_rate', 850);
    }

    public function test_mobile_technician_task_idempotency()
    {
        $this->actingAs($this->user);

        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Suspension oil leak',
            'estimated_cost' => 500,
            'service_date' => now(),
            'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
        ]);

        $task = JobCardTask::create([
            'job_card_id' => $jobCard->id,
            'name' => 'Inspect Shock Absorbers',
            'status' => 'pending',
            'estimated_minutes' => 30,
        ]);

        // 1. Success on first try
        $token = 'idempotent_task_token_' . uniqid();
        $response1 = $this->withHeaders(['X-Idempotency-Token' => $token])
            ->putJson("/api/v1/mobile/tasks/{$task->id}/status", [
                'status' => 'in_progress'
            ]);
        $response1->assertStatus(200);
        $response1->assertJsonPath('data.status', 'in_progress');

        // 2. Conflict blocked on second try
        $response2 = $this->withHeaders(['X-Idempotency-Token' => $token])
            ->putJson("/api/v1/mobile/tasks/{$task->id}/status", [
                'status' => 'in_progress'
            ]);
        $response2->assertStatus(409);
        $response2->assertJsonPath('success', false);
    }

    public function test_supervisor_dashboard_escalations_and_telemetry()
    {
        $this->actingAs($this->user);

        // Preseed a workshop bay
        $bayId = DB::table('workshop_bays')->insertGetId([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bay 10',
            'code' => 'BAY-10',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create a job card in ready_for_qc status older than 65 minutes
        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Brake pad replacement',
            'estimated_cost' => 800,
            'service_date' => now(),
            'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
        ]);

        DB::table('job_cards')->where('id', $jobCard->id)->update([
            'service_status' => 'ready_for_qc',
            'updated_at' => Carbon::now()->subMinutes(65)
        ]);

        // Check supervisor dashboard telemetry
        $response = $this->getJson('/api/v1/system/operations/supervisor-dashboard');
        $response->assertStatus(200);

        // Check if ready_for_qc delay triggers HIGH alert and P2 escalation workflow
        $response->assertJsonFragment([
            'job_card_id' => $jobCard->id,
            'alert_level' => 'HIGH',
            'escalation_status' => 'P2_escalation_workflow_active'
        ]);

        // Verify P2 system health alert is inserted
        $this->assertTrue(DB::table('system_health_alerts')
            ->where('alert_type', 'P2_delayed_qc')
            ->where('severity', 'warning')
            ->exists());
    }

    public function test_webhook_replay_protection()
    {
        $this->actingAs($this->user);

        $eventId = 'evt_test_' . uniqid();

        // 1. Success on first post
        $response1 = $this->postJson('/api/v1/system/operations/webhook-receiver', [
            'event_id' => $eventId,
            'amount' => 5000,
            'status' => 'completed'
        ]);
        $response1->assertStatus(200);
        $response1->assertJsonPath('success', true);

        // 2. Reject replay attempt
        $response2 = $this->postJson('/api/v1/system/operations/webhook-receiver', [
            'event_id' => $eventId,
            'amount' => 5000,
            'status' => 'completed'
        ]);
        $response2->assertStatus(409);
        $response2->assertJsonPath('success', false);

        // Verify audit log has recorded the blocked webhook replay
        $this->assertTrue(DB::table('audit_logs')
            ->where('action', 'webhook_replay_blocked')
            ->whereJsonContains('details->event_id', $eventId)
            ->exists());
    }

    public function test_explainable_ai_load_balancing_telemetry()
    {
        $this->actingAs($this->user);

        // Setup a congested Bay 1 (with 4 job cards) and idle Bay 2
        $bay1 = DB::table('workshop_bays')->insertGetId([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bay 1',
            'code' => 'BAY-1',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $bay2 = DB::table('workshop_bays')->insertGetId([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bay 2',
            'code' => 'BAY-2',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        for ($i = 0; $i < 4; $i++) {
            JobCard::create([
                'tenant_id' => $this->tenant->id,
                'customer_id' => $this->customer->id,
                'vehicle_id' => $this->vehicle->id,
                'workshop_bay_id' => $bay1,
                'assigned_mechanic_id' => $this->user->id,
                'complaint' => 'Service ' . $i,
                'estimated_cost' => 300,
                'service_date' => now(),
                'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
            ]);
        }

        $response = $this->getJson('/api/v1/system/operations/load-balancing');
        $response->assertStatus(200);

        // Ensure explainability and safety_corridor details are returned
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'type',
                    'confidence_score',
                    'explanation',
                    'explainability' => [
                        'base_score',
                        'calibration_multiplier',
                        'raw_calibrated_score',
                        'reason'
                    ],
                    'safety_corridor' => [
                        'min_confidence_floor',
                        'max_confidence_ceiling',
                        'min_multiplier_floor',
                        'max_multiplier_ceiling',
                        'floor_triggered',
                        'ceiling_triggered'
                    ]
                ]
            ]
        ]);
    }
}
