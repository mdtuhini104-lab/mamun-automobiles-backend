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

class OperationalExcellenceTest extends TestCase
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
        $this->tenant = Tenant::create(['company_name' => 'Excellence Corp', 'domain' => 'excellence.erp']);
        $this->branch = Branch::create(['tenant_id' => $this->tenant->id, 'name' => 'Dhaka East']);

        // 2. Setup user and assign Super Admin role
        $this->user = new User([
            'name' => 'Excellence Director',
            'email' => 'director@excellence.erp',
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
            'name' => 'Excellent Customer',
            'phone' => '01811223344',
            'email' => 'customer@excellence.com',
            'address' => 'Dhaka, Bangladesh',
            'tag' => 'Corporate'
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
            'model' => 'Prius',
            'year' => 2018,
            'license_plate' => 'DHK-22-3333',
        ]);

        // 5. Create employee/technician
        $dept = Department::create(['name' => 'Excellence Operations']);
        $desg = Designation::create(['name' => 'Master Mechanic', 'department_id' => $dept->id]);
        
        $this->employee = new Employee([
            'employee_code' => 'EMP-EXC-01',
            'first_name' => 'Basher',
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

        // 6. Create workshop bay
        DB::table('workshop_bays')->insert([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bay 1',
            'code' => 'BAY-1',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function test_excellence_kpis_endpoint_returns_valid_structure()
    {
        $this->actingAs($this->user);

        // Preseed a completed job card and task to calculate efficiencies
        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Regular engine check',
            'estimated_cost' => 500,
            'service_date' => now(),
            'service_status' => 'completed',
        ]);

        $task = new JobCardTask([
            'job_card_id' => $jobCard->id,
            'name' => 'Completed Task',
            'status' => 'completed',
            'estimated_minutes' => 60,
        ]);
        $task->actual_minutes = 50; // 60/50 = 120% efficiency
        $task->save();

        // Preseed invoice
        DB::table('invoices')->insert([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'job_card_id' => $jobCard->id,
            'invoice_number' => 'INV-EXC-01',
            'grand_total' => 1500.00,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson('/api/v1/system/operations/excellence-kpis');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'workshop_analytics' => [
                    'average_job_completion_minutes',
                    'comeback_job_frequency',
                    'average_quotation_approval_delay_minutes',
                    'average_customer_payment_delay_minutes'
                ],
                'operational_kpis' => [
                    'branch_efficiency',
                    'technician_efficiency',
                    'quotation_conversion_rate',
                    'customer_approval_rate',
                    'repeat_customer_rate',
                    'revenue_per_technician' => [
                        'gross',
                        'net'
                    ],
                    'revenue_per_bay' => [
                        'gross',
                        'net'
                    ],
                    'delayed_workflow_ratio',
                    'burnout_risk_trend',
                    'support_dependency_trend'
                ],
                'ai_metrics' => [
                    'recommendation_usefulness_score',
                    'operational_trust_index',
                    'ai_coordination_stability_index',
                    'acceptance_rate',
                    'total_recommendations',
                    'approved_count'
                ],
                'realtime_telemetry' => [
                    'websocket_stability',
                    'queue_recovery_duration_seconds',
                    'offline_sync_latency_seconds',
                    'realtime_dashboard_refresh_ms',
                    'technician_idle_ratio',
                    'supervisor_intervention_frequency'
                ],
                'branch_scaling' => [
                    'branch_operational_consistency_score',
                    'tenant_health_stability_score',
                    'onboarding_success_rate',
                    'branch_congestion_trends'
                ]
            ]
        ]);

        // Assert efficiency calculations
        $response->assertJsonPath('data.operational_kpis.branch_efficiency', 120);
        $response->assertJsonPath('data.operational_kpis.revenue_per_technician.net', 1500);
        $response->assertJsonPath('data.operational_kpis.revenue_per_bay.net', 1500);
    }

    public function test_quotation_conversion_rate_excludes_supervisor_corrections()
    {
        $this->actingAs($this->user);

        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Quotation check',
            'estimated_cost' => 100,
            'service_date' => now(),
            'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
        ]);

        // 1. Preseed a quotation cancelled within 3 minutes of creation (excluded)
        $quoteId1 = DB::table('quotations')->insertGetId([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'job_card_id' => $jobCard->id,
            'quotation_number' => 'Q-EXC-01',
            'status' => 'cancelled',
            'grand_total' => 200,
            'created_at' => Carbon::now()->subMinutes(10),
            'updated_at' => Carbon::now()->subMinutes(7), // diff = 3 minutes
        ]);

        // 2. Preseed a quotation cancelled after 15 minutes of creation (included in total quotes)
        $quoteId2 = DB::table('quotations')->insertGetId([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'job_card_id' => $jobCard->id,
            'quotation_number' => 'Q-EXC-02',
            'status' => 'cancelled',
            'grand_total' => 200,
            'created_at' => Carbon::now()->subMinutes(30),
            'updated_at' => Carbon::now()->subMinutes(15), // diff = 15 minutes
        ]);

        // 3. Preseed an approved quotation (included)
        $quoteId3 = DB::table('quotations')->insertGetId([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'job_card_id' => $jobCard->id,
            'quotation_number' => 'Q-EXC-03',
            'status' => 'approved',
            'grand_total' => 500,
            'created_at' => Carbon::now()->subMinutes(20),
            'updated_at' => Carbon::now()->subMinutes(18),
        ]);

        $response = $this->getJson('/api/v1/system/operations/excellence-kpis');
        $response->assertStatus(200);

        // Under policy:
        // Total included quotations: 2 (Quote 2 and Quote 3. Quote 1 is ignored).
        // Approved quotations: 1 (Quote 3).
        // Expected quotation conversion rate = 1/2 * 100 = 50.0%
        $response->assertJsonPath('data.operational_kpis.quotation_conversion_rate', 50);
    }

    public function test_comeback_job_frequency_scans_30_day_windows()
    {
        $this->actingAs($this->user);

        // Preseed a completed job card on day 1
        $jobCard1 = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Suspension check',
            'estimated_cost' => 500,
            'service_date' => now(),
            'service_status' => 'completed',
        ]);
        DB::table('job_cards')->where('id', $jobCard1->id)->update([
            'created_at' => Carbon::now()->subDays(25),
            'updated_at' => Carbon::now()->subDays(25)
        ]);

        // Preseed a new job card for same vehicle 15 days later (comeback job!)
        $jobCard2 = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Suspension noise again',
            'estimated_cost' => 500,
            'service_date' => now(),
            'service_status' => \App\Enums\ServiceStatus::IN_PROGRESS,
        ]);
        DB::table('job_cards')->where('id', $jobCard2->id)->update([
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(10)
        ]);

        $response = $this->getJson('/api/v1/system/operations/excellence-kpis');
        $response->assertStatus(200);

        // Verify that 1 comeback job is detected
        $response->assertJsonPath('data.workshop_analytics.comeback_job_frequency', 1);
    }
}
