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

class AutonomousObservationTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $branchA;
    protected $branchB;
    protected $user;
    protected $customer;
    protected $vehicle;
    protected $employeeA;
    protected $employeeB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup multi-tenant and branch structure
        $this->tenant = Tenant::create(['company_name' => 'Observation Corp', 'domain' => 'obs.erp']);
        $this->branchA = Branch::create(['tenant_id' => $this->tenant->id, 'name' => 'Dhaka West']);
        $this->branchB = Branch::create(['tenant_id' => $this->tenant->id, 'name' => 'Chittagong Port']);

        // 2. Setup user and assign Super Admin role
        $this->user = new User([
            'name' => 'Observation Director',
            'email' => 'director@obs.erp',
            'password' => bcrypt('password123'),
        ]);
        $this->user->tenant_id = $this->tenant->id;
        $this->user->branch_id = $this->branchA->id;
        $this->user->save();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
        $this->user->assignRole('Super Admin');

        // 3. Create customer
        $this->customer = new Customer([
            'name' => 'Obs Customer',
            'phone' => '01899887755',
            'email' => 'customer@obs.com',
            'address' => 'Dhaka, Bangladesh',
            'tag' => 'Corporate'
        ]);
        $this->customer->tenant_id = $this->tenant->id;
        $this->customer->branch_id = $this->branchA->id;
        $this->customer->save();

        // 4. Create vehicle
        $this->vehicle = Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'make' => 'Nissan',
            'model' => 'X-Trail',
            'year' => 2019,
            'license_plate' => 'DHK-33-4444',
        ]);

        // 5. Create employees for branch A and B
        $dept = Department::create(['name' => 'Repair Ops']);
        $desg = Designation::create(['name' => 'Specialist', 'department_id' => $dept->id]);
        
        $this->employeeA = new Employee([
            'employee_code' => 'EMP-OBS-01',
            'first_name' => 'Arif',
            'last_name' => 'Mechanic',
            'status' => 'active',
            'availability_status' => 'available'
        ]);
        $this->employeeA->tenant_id = $this->tenant->id;
        $this->employeeA->branch_id = $this->branchA->id;
        $this->employeeA->user_id = $this->user->id;
        $this->employeeA->department_id = $dept->id;
        $this->employeeA->designation_id = $desg->id;
        $this->employeeA->save();

        $this->employeeB = new Employee([
            'employee_code' => 'EMP-OBS-02',
            'first_name' => 'Babul',
            'last_name' => 'Mechanic',
            'status' => 'active',
            'availability_status' => 'available'
        ]);
        $this->employeeB->tenant_id = $this->tenant->id;
        $this->employeeB->branch_id = $this->branchB->id;
        
        $userB = new User([
            'name' => 'Babul Mechanic',
            'email' => 'babul@obs.erp',
            'password' => bcrypt('password123'),
        ]);
        $userB->tenant_id = $this->tenant->id;
        $userB->branch_id = $this->branchB->id;
        $userB->save();
        $this->employeeB->user_id = $userB->id;

        $this->employeeB->department_id = $dept->id;
        $this->employeeB->designation_id = $desg->id;
        $this->employeeB->save();

        // 6. Create workshop bays for branch A and B
        DB::table('workshop_bays')->insert([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'name' => 'Bay A',
            'code' => 'BAY-A',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('workshop_bays')->insert([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchB->id,
            'name' => 'Bay B',
            'code' => 'BAY-B',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function test_executive_observation_endpoint_structure_and_markdown()
    {
        $this->actingAs($this->user);

        // Preseed completed job cards and invoices to populate comparison metrics
        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'AC Maintenance',
            'estimated_cost' => 500,
            'service_date' => now(),
            'service_status' => 'completed',
        ]);

        DB::table('invoices')->insert([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'job_card_id' => $jobCard->id,
            'invoice_number' => 'INV-OBS-01',
            'grand_total' => 500.00,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson('/api/v1/system/operations/executive-observation');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'executive_summary_markdown',
                'overall_health_summary',
                'branch_comparisons' => [
                    '*' => [
                        'branch_id',
                        'branch_name',
                        'revenue_per_technician',
                        'jobs_per_bay',
                        'conversions_per_active_customer',
                        'supervisor_interventions_per_100_jobs',
                        'burnout_risk_per_technician',
                        'congestion_ratio_per_bay',
                        'raw_revenue_gross',
                        'raw_revenue_net'
                    ]
                ],
                'degradation_observations' => [
                    '*' => [
                        'type',
                        'severity',
                        'message',
                        'metrics'
                    ]
                ],
                'recommendation_accountability' => [
                    'acceptance_rate',
                    'total_recommendations',
                    'ignored_rate',
                    'failed_routing_ratio',
                    'drift_protection_status'
                ],
                'executive_suggestions'
            ]
        ]);

        // Verify markdown content returns valid headers
        $this->assertStringContainsString('# Executive Summary', $response->json('data.executive_summary_markdown'));
        $this->assertStringContainsString('## Branch Comparisons', $response->json('data.executive_summary_markdown'));
        $this->assertStringContainsString('## Operational Degradation Findings', $response->json('data.executive_summary_markdown'));
    }

    public function test_cross_branch_normalized_comparison_math()
    {
        $this->actingAs($this->user);

        // Branch A: 1 Tech (Arif), 1 Bay (Bay A), 1 Invoice of 500. Job cards = 1.
        $jobCardA = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Suspension check',
            'estimated_cost' => 500,
            'service_date' => now(),
            'service_status' => 'completed',
        ]);
        DB::table('invoices')->insert([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'job_card_id' => $jobCardA->id,
            'invoice_number' => 'INV-A',
            'grand_total' => 500.00,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Branch B: 1 Tech (Babul), 1 Bay (Bay B), 1 Invoice of 2000. Job cards = 1.
        $jobCardB = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchB->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Engine rebuild',
            'estimated_cost' => 2000,
            'service_date' => now(),
            'service_status' => 'completed',
        ]);
        DB::table('invoices')->insert([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchB->id,
            'customer_id' => $this->customer->id,
            'job_card_id' => $jobCardB->id,
            'invoice_number' => 'INV-B',
            'grand_total' => 2000.00,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson('/api/v1/system/operations/executive-observation');
        $response->assertStatus(200);

        // Extract branch comparisons array
        $branches = collect($response->json('data.branch_comparisons'));
        $branchAData = $branches->firstWhere('branch_name', 'Dhaka West');
        $branchBData = $branches->firstWhere('branch_name', 'Chittagong Port');

        // Normalization checks:
        // Branch A: Revenue/Tech = 500 / 1 tech = 500
        $this->assertEquals(500, $branchAData['revenue_per_technician']);
        // Branch B: Revenue/Tech = 2000 / 1 tech = 2000
        $this->assertEquals(2000, $branchBData['revenue_per_technician']);
    }

    public function test_degradation_alert_severity_rankings()
    {
        $this->actingAs($this->user);

        // Seed unbilled completed job cards to trigger a revenue leakage warning (Estimated 1000, Invoiced 0 -> 100% leakage -> CRITICAL)
        JobCard::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Major engine overhaul',
            'estimated_cost' => 1000,
            'service_date' => now(),
            'service_status' => 'completed',
        ]);

        $response = $this->getJson('/api/v1/system/operations/executive-observation');
        $response->assertStatus(200);

        $degradations = collect($response->json('data.degradation_observations'));
        $leakageAlert = $degradations->firstWhere('type', 'revenue_leakage');

        $this->assertNotNull($leakageAlert);
        $this->assertEquals('CRITICAL', $leakageAlert['severity']);
    }
}
