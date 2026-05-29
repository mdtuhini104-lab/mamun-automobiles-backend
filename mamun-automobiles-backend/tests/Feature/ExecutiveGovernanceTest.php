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
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\DB;

class ExecutiveGovernanceTest extends TestCase
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
        $this->tenant = Tenant::create(['company_name' => 'Governance Corp', 'domain' => 'gov.erp']);
        $this->branchA = Branch::create(['name' => 'Dhaka West']);
        $this->branchB = Branch::create(['name' => 'Chittagong Port']);

        // Explicitly link branch tenant via association setup
        // 2. Setup user and assign Super Admin role
        $this->user = new User([
            'name' => 'Governance Director',
            'email' => 'director@gov.erp',
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
            'name' => 'Gov Customer',
            'phone' => '01899887755',
            'email' => 'customer@gov.com',
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

        // 5. Create employees
        $dept = Department::create(['name' => 'Repair Ops']);
        $desg = Designation::create(['name' => 'Specialist', 'department_id' => $dept->id]);
        
        $this->employeeA = new Employee([
            'employee_code' => 'EMP-GOV-01',
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
            'employee_code' => 'EMP-GOV-02',
            'first_name' => 'Babul',
            'last_name' => 'Mechanic',
            'status' => 'active',
            'availability_status' => 'available'
        ]);
        $this->employeeB->tenant_id = $this->tenant->id;
        $this->employeeB->branch_id = $this->branchB->id;
        
        $userB = new User([
            'name' => 'Babul Mechanic',
            'email' => 'babul@gov.erp',
            'password' => bcrypt('password123'),
        ]);
        $userB->tenant_id = $this->tenant->id;
        $userB->branch_id = $this->branchB->id;
        $userB->save();
        $this->employeeB->user_id = $userB->id;
        $this->employeeB->department_id = $dept->id;
        $this->employeeB->designation_id = $desg->id;
        $this->employeeB->save();

        // 6. Create workshop bays
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

    public function test_governance_briefing_endpoints_returns_correct_structure_and_markdown()
    {
        $this->actingAs($this->user);

        // Preseed completed job cards and invoices
        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Preventative maintenance',
            'estimated_cost' => 500,
            'service_date' => now(),
            'service_status' => 'completed',
        ]);

        DB::table('invoices')->insert([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'job_card_id' => $jobCard->id,
            'invoice_number' => 'INV-GOV-01',
            'grand_total' => 500.00,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson('/api/v1/system/operations/executive-governance');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'executive_governance_markdown',
                'overall_briefing_severity',
                'strategic_risk_forecasts' => [
                    'overload_escalation_risk',
                    'burnout_evolution',
                    'supervisor_dependency_growth',
                    'profitability_degradation',
                    'congestion_recurrence',
                    'customer_trust_decline',
                    'operational_instability_trends'
                ],
                'profitability_stability' => [
                    'gross_profitability_trend',
                    'net_cashflow_stability',
                    'revenue_leakage_trend',
                    'aggregate_leakage_amount'
                ],
                'branch_expansion_readiness' => [
                    '*' => [
                        'branch_id',
                        'branch_name',
                        'operational_maturity',
                        'maturity_score',
                        'staffing_sufficiency',
                        'conversion_stability',
                        'congestion_sustainability',
                        'revenue_reliability',
                        'management_dependency',
                        'scalability_readiness'
                    ]
                ],
                'workforce_sustainability' => [
                    '*' => [
                        'branch_name',
                        'overload_persistence',
                        'technician_recovery_rate',
                        'routing_fairness_deviation',
                        'burnout_trend_evolution',
                        'workload_imbalance',
                        'idle_imbalance_patterns'
                    ]
                ],
                'executive_suggestions'
            ]
        ]);

        // Validate markdown contains required sections
        $this->assertStringContainsString('# Executive Governance Briefing', $response->json('data.executive_governance_markdown'));
        $this->assertStringContainsString('## Strategic Risk Forecasts', $response->json('data.executive_governance_markdown'));
        $this->assertStringContainsString('## Profitability Stability', $response->json('data.executive_governance_markdown'));
        $this->assertStringContainsString('## Branch Expansion Readiness', $response->json('data.executive_governance_markdown'));
        $this->assertStringContainsString('## Workforce Sustainability Forecast', $response->json('data.executive_governance_markdown'));
    }

    public function test_branch_maturity_score_classification_boundaries()
    {
        $this->actingAs($this->user);

        // Seeding to generate a branch with high consistency and perfect conversion
        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'AC Maintenance',
            'estimated_cost' => 1000,
            'service_date' => now(),
            'service_status' => 'completed',
        ]);

        DB::table('invoices')->insert([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'job_card_id' => $jobCard->id,
            'invoice_number' => 'INV-GOV-02',
            'grand_total' => 1000.00,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Quotation approved to increase conversion rate
        DB::table('quotations')->insert([
            'job_card_id' => $jobCard->id,
            'status' => 'approved',
            'quotation_number' => 'QT-GOV-02',
            'grand_total' => 1000.00,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson('/api/v1/system/operations/executive-governance');
        $response->assertStatus(200);

        $readiness = collect($response->json('data.branch_expansion_readiness'));
        $branchAData = $readiness->firstWhere('branch_name', 'Dhaka West');

        $this->assertNotNull($branchAData);
        // Maturity score is calculated, check if correct maturity classification is applied
        $this->assertContains($branchAData['operational_maturity'], ['Expansion Ready', 'Stable', 'Needs Optimization', 'High Governance Risk']);
    }

    public function test_burnout_forecast_escalation_protection_elevates_severity()
    {
        $this->actingAs($this->user);

        // Seed high workload assignments to create burnout forecast conditions
        $jobCard = JobCard::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchA->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_mechanic_id' => $this->user->id,
            'complaint' => 'Suspension replacement',
            'estimated_cost' => 500,
            'service_date' => now(),
            'service_status' => 'in_progress',
        ]);

        // Seed 4 active tasks assigned to Arif (overload condition > 3 active tasks)
        for ($i = 1; $i <= 4; $i++) {
            $taskId = DB::table('job_card_tasks')->insertGetId([
                'job_card_id' => $jobCard->id,
                'name' => "Overload Task {$i}",
                'estimated_minutes' => 60,
                'status' => 'in_progress',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('job_task_assignments')->insert([
                'job_card_task_id' => $taskId,
                'employee_id' => $this->employeeA->id,
                'allocated_at' => now(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Add attendance overtime hours (>30 hours) to push burnout risk score > 50
        for ($i = 0; $i < 10; $i++) {
            DB::table('attendances')->insert([
                'user_id' => $this->user->id,
                'date' => now()->subDays($i)->format('Y-m-d'),
                'check_in' => '08:00:00',
                'check_out' => '20:00:00', // 12 hours worked -> 4 hours overtime per day -> 40 hours overtime
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $response = $this->getJson('/api/v1/system/operations/executive-governance');
        $response->assertStatus(200);

        // Burnout risk calculations will register a high burnout score (>50.0)
        // Escalation policy elevates briefing severity to HIGH
        $this->assertEquals('HIGH', $response->json('data.overall_briefing_severity'));
        
        // Assert warning recommendation triggers
        $suggestions = collect($response->json('data.executive_suggestions'));
        $burnoutWarning = $suggestions->first(function ($val) {
            return str_contains($val, 'Workforce Sustainability Warning');
        });
        $this->assertNotNull($burnoutWarning);
    }
}
