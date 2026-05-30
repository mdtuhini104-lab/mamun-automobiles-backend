<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ErpStabilizationTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_authentication_endpoint()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@mamunerp.com',
            'password' => 'admin123',
        ]);
        
        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response->json('data'));
    }

    public function test_transaction_rollback_on_failed_job_card()
    {
        $user = \App\Models\User::first();
        $this->actingAs($user);

        $customer = \App\Models\Customer::first() ?: \App\Models\Customer::create([
            'name' => 'Test Customer',
            'phone' => '01700000000',
            'email' => 'test@example.com'
        ]);

        $vehicle = \App\Models\Vehicle::first() ?: \App\Models\Vehicle::create([
            'customer_id' => $customer->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'license_plate' => 'DHK-12-3456',
            'vin' => 'JT123456789',
            'engine_number' => '1NZ-FE1234',
            'color' => 'White',
            'fuel_type' => 'Petrol',
        ]);

        $jobCard = \App\Models\JobCard::first() ?: \App\Models\JobCard::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_mechanic_id' => $user->id,
            'complaint' => 'Brakes squeaking and oil needs change.',
            'estimated_cost' => 2500,
            'service_date' => now(),
        ]);

        $part = \App\Models\Part::first() ?: \App\Models\Part::create([
            'name' => 'Oil Filter',
            'sku' => 'PRT-001',
            'brand' => 'Bosch',
            'cost_price' => 150,
            'sale_price' => 250,
            'stock_quantity' => 50,
            'low_stock_threshold' => 10,
        ]);
        
        $initialStock = $part->stock_quantity;
        
        $response = $this->postJson("/api/v1/job-cards/{$jobCard->id}/items", [
            'part_id' => $part->id,
            'quantity' => $initialStock + 10, // Exceeds stock
            'unit_price' => 100
        ]);
        
        $response->assertStatus(422); // Or 500 depending on exact exception handler catch
        
        // Assert stock not deducted (transaction rolled back)
        $this->assertEquals($initialStock, $part->fresh()->stock_quantity);
    }

    public function test_health_check_endpoint()
    {
        $response = $this->getJson('/api/v1/health');
        $response->assertStatus(200);
        $this->assertEquals('connected', $response->json('services.database'));
    }

    public function test_response_timing_middleware()
    {
        $response = $this->getJson('/api/v1/health');
        $response->assertHeader('X-Response-Time');
        $response->assertHeader('X-Correlation-ID');
    }

    public function test_dashboard_analytics_stress()
    {
        $user = \App\Models\User::first();
        $this->actingAs($user);
        
        for ($i = 0; $i < 5; $i++) {
            $response = $this->getJson('/api/v1/dashboard');
            $response->assertStatus(200);
        }
    }

    public function test_appointment_scheduling_and_conflicts()
    {
        $tenant = \App\Models\Tenant::create(['company_name' => 'Mamun Automobiles', 'domain' => 'mamun.erp']);
        $branch = \App\Models\Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Dhaka Branch'
        ]);

        $user = \App\Models\User::first();
        $user->update([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id
        ]);
        $this->actingAs($user);

        $customer = \App\Models\Customer::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'John Doe',
            'phone' => '01800000000',
            'email' => 'john@example.com'
        ]);

        $vehicle = \App\Models\Vehicle::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'make' => 'Nissan',
            'model' => 'Sunny',
            'year' => 2012,
            'license_plate' => 'DHK-99-9999',
            'vin' => 'NS123456789',
            'engine_number' => 'QG15-FE1234',
            'color' => 'Silver',
            'fuel_type' => 'CNG',
        ]);

        // Find a Thursday so it is not a weekly Friday holiday
        $thursday = \Carbon\Carbon::now()->next(\Carbon\Carbon::THURSDAY)->format('Y-m-d');
        $friday = \Carbon\Carbon::now()->next(\Carbon\Carbon::FRIDAY)->format('Y-m-d');

        $scheduler = app(\App\Services\AppointmentSchedulerService::class);

        // 1. Success path booking
        $appointment = $scheduler->reserveSlot([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_date' => $thursday,
            'appointment_time' => '10:00:00',
            'service_type' => 'Engine Tune-up'
        ]);

        $this->assertNotNull($appointment->id);
        $this->assertEquals('pending', $appointment->status);

        // 2. Assert double booking is blocked
        $this->expectException(\Exception::class);
        $scheduler->reserveSlot([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_date' => $thursday,
            'appointment_time' => '11:00:00',
            'service_type' => 'AC Check'
        ]);
    }

    public function test_appointment_holiday_blocked()
    {
        $tenant = \App\Models\Tenant::create(['company_name' => 'Mamun Automobiles', 'domain' => 'mamun.erp']);
        $branch = \App\Models\Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Dhaka Branch'
        ]);

        $user = \App\Models\User::first();
        $user->update([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id
        ]);
        $this->actingAs($user);

        $friday = \Carbon\Carbon::now()->next(\Carbon\Carbon::FRIDAY)->format('Y-m-d');
        $scheduler = app(\App\Services\AppointmentSchedulerService::class);

        $customer = \App\Models\Customer::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'John Doe',
            'phone' => '01800000000',
            'email' => 'john@example.com'
        ]);
        
        $this->expectException(\Exception::class);
        $scheduler->reserveSlot([
            'customer_id' => $customer->id,
            'appointment_date' => $friday,
            'appointment_time' => '10:00:00',
            'service_type' => 'Engine Tune-up'
        ]);
    }

    public function test_backup_recovery_engine_and_aes_encryption()
    {
        $engine = app(\App\Services\BackupRecoveryEngine::class);

        // 1. Test create archive
        $archivePath = $engine->createArchive();
        $this->assertFileExists($archivePath);

        // 2. Test encryption
        $encryptedPath = $engine->gpgEncrypt($archivePath);
        $this->assertFileExists($encryptedPath);
        $this->assertFileDoesNotExist($archivePath); // Original is cleaned

        // 3. Test verification / decryption
        $isViable = $engine->verifyRestore($encryptedPath);
        $this->assertTrue($isViable);

        // Clean up
        if (file_exists($encryptedPath)) {
            unlink($encryptedPath);
        }
    }

    public function test_support_ticket_creation_and_multitenant_isolation()
    {
        $tenant1 = \App\Models\Tenant::create(['company_name' => 'Company A', 'domain' => 'comp-a.erp']);
        $tenant2 = \App\Models\Tenant::create(['company_name' => 'Company B', 'domain' => 'comp-b.erp']);

        $user1 = new \App\Models\User([
            'name' => 'Tenant 1 User',
            'email' => 'user1@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user1->tenant_id = $tenant1->id;
        $user1->save();

        $user2 = new \App\Models\User([
            'name' => 'Tenant 2 User',
            'email' => 'user2@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user2->tenant_id = $tenant2->id;
        $user2->save();

        // 1. Submit ticket as User 1
        $this->actingAs($user1);
        $response = $this->postJson('/api/v1/support/tickets', [
            'title' => 'Billing problem User 1',
            'description' => 'I cannot pay via Stripe.',
            'priority' => 'high',
            'category' => 'billing'
        ]);
        $response->assertStatus(201);
        $ticketId = $response->json('ticket_id');

        // Verify isolation: User 1 sees the ticket
        $response1 = $this->getJson('/api/v1/support/tickets');
        $response1->assertStatus(200);
        $this->assertCount(1, $response1->json('data'));
        $this->assertEquals('Billing problem User 1', $response1->json('data.0.title'));

        // Verify isolation: User 2 cannot see User 1's ticket
        $this->actingAs($user2);
        $response2 = $this->getJson('/api/v1/support/tickets');
        $response2->assertStatus(200);
        $this->assertCount(0, $response2->json('data'));
    }

    public function test_support_ticket_incident_workflow()
    {
        $tenant = \App\Models\Tenant::create(['company_name' => 'Support Testing Inc', 'domain' => 'support.erp']);
        $user = new \App\Models\User([
            'name' => 'Support Tech',
            'email' => 'tech@support.erp',
            'password' => bcrypt('password123'),
        ]);
        $user->tenant_id = $tenant->id;
        $user->save();
        $this->actingAs($user);

        // 1. Submit a Support Ticket
        $ticket = \App\Models\SupportTicket::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'title' => 'Slow Database Queries in Reports',
            'description' => 'Retrieving financial statements is taking over 10 seconds.',
            'priority' => 'high',
            'category' => 'technical',
            'status' => 'open'
        ]);

        // 2. Map Support Ticket to Incident
        $responseIncident = $this->postJson("/api/v1/support/tickets/{$ticket->id}/incident", [
            'title' => 'Slow database connection scaling lag',
            'description' => 'Unindexed queries on cashbooks transactions are stalling DB threads.',
            'severity' => 'high'
        ]);
        $responseIncident->assertStatus(201);
        $incidentId = $responseIncident->json('data.id');

        // 3. Log Resolution Workflow steps
        $responseWorkflow = $this->postJson("/api/v1/support/incidents/{$incidentId}/workflow", [
            'steps' => [
                'Analyze query profiling slow logs',
                'Identify missing composite indexes on tenant_id, entry_date',
                'Create and run Laravel migrations adding missing database indexing constraints'
            ],
            'solution' => 'Added double composite index constraints on financial transactions, dropping query times from 10.4s to 0.05s.',
            'resolve_incident' => true
        ]);
        $responseWorkflow->assertStatus(200);
        $workflowId = $responseWorkflow->json('data.id');

        // Assert ticket status is updated to resolved
        $this->assertEquals('resolved', $ticket->fresh()->status);

        // 4. Publish Resolution Workflow to dynamic Knowledge Base Article
        $responseKb = $this->postJson("/api/v1/support/workflows/{$workflowId}/publish-kb", [
            'title' => 'How to Index and Optimize Slow Financial Reports Queries',
            'category' => 'technical',
            'is_global' => true
        ]);
        $responseKb->assertStatus(201);
        $slug = $responseKb->json('data.slug');

        // 5. Search KB Articles
        $responseSearch = $this->getJson('/api/v1/support/kb?search=Optimize');
        $responseSearch->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($responseSearch->json('data')));

        // 6. View Specific Article
        $responseArticle = $this->getJson("/api/v1/support/kb/{$slug}");
        $responseArticle->assertStatus(200);
        $this->assertEquals('How to Index and Optimize Slow Financial Reports Queries', $responseArticle->json('data.title'));
    }

    public function test_diagnostics_export_safety()
    {
        $tenant = \App\Models\Tenant::create(['company_name' => 'Security Audit Org', 'domain' => 'audit.erp']);
        $user = new \App\Models\User([
            'name' => 'Audit Admin',
            'email' => 'audit@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->tenant_id = $tenant->id;
        $user->save();
        $this->actingAs($user);

        // Set sensitive variables in $_ENV for sanitization checking
        $_ENV['DB_PASSWORD'] = 'super-secret-password-123';
        $_ENV['STRIPE_SECRET'] = 'sk_live_abc123';
        $_ENV['BKASH_APP_SECRET'] = 'bkash-app-secret-xyz';

        $response = $this->getJson('/api/v1/support/diagnostics');
        $response->assertStatus(200);
        $diagnostics = $response->json('diagnostics');

        // Verify that secrets are fully redacted from the diagnostics output
        $this->assertNotEquals('super-secret-password-123', $diagnostics['app_config_sanitized']['database']['connections']['mysql']['password'] ?? '');
        $this->assertEquals('[REDACTED_SECURE_VAULT_METRIC]', $diagnostics['environment']['DB_PASSWORD'] ?? '');
    }

    public function test_stress_endpoints_production_gating_and_roles()
    {
        // Explicitly create Super Admin role across all guards to prevent RoleDoesNotExist exceptions
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'sanctum']);

        $tenant = \App\Models\Tenant::create(['company_name' => 'Stress Test Corp', 'domain' => 'stress.erp']);
        $user = new \App\Models\User([
            'name' => 'Operator Admin',
            'email' => 'operator@stress.erp',
            'password' => bcrypt('password123'),
        ]);
        $user->tenant_id = $tenant->id;
        $user->save();

        // 1. Non-Super Admin must get 403 Forbidden
        $this->actingAs($user);
        $response = $this->postJson('/api/v1/system/stress/slow-query', ['milliseconds' => 50]);
        $response->assertStatus(403);

        // Assign Super Admin role
        $user->assignRole('Super Admin');

        // 2. Super Admin in testing environment must get success (since env is 'testing' i.e. != 'production')
        $response2 = $this->postJson('/api/v1/system/stress/slow-query', ['milliseconds' => 50]);
        $response2->assertStatus(200);
        $this->assertTrue($response2->json('success'));

        // Assert audit log was recorded
        $this->assertTrue(\Illuminate\Support\Facades\DB::table('audit_logs')
            ->where('tenant_id', $tenant->id)
            ->where('action', 'stress_test_slow_query')
            ->exists());

        // 3. Super Admin when environment is mocked to 'production' must get 403 Forbidden
        $originalEnv = app()->environment();
        app()->detectEnvironment(function () {
            return 'production';
        });
        config(['app.env' => 'production']);

        $response3 = $this->postJson('/api/v1/system/stress/slow-query', ['milliseconds' => 50]);
        $response3->assertStatus(403);

        // Restore environment settings
        app()->detectEnvironment(function () use ($originalEnv) {
            return $originalEnv;
        });
        config(['app.env' => $originalEnv]);
    }

    public function test_workflow_suggestions()
    {
        $tenant = \App\Models\Tenant::create(['company_name' => 'Suggestions Corp', 'domain' => 'sug.erp']);
        $user = \App\Models\User::first();
        $user->tenant_id = $tenant->id;
        $user->save();
        $this->actingAs($user);
        $tenantId = $tenant->id;

        // Create a workshop bay
        \Illuminate\Support\Facades\DB::table('workshop_bays')->insert([
            'tenant_id' => $tenantId,
            'name' => 'Bay 99',
            'code' => 'B99',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create an employee with matching schema columns
        \Illuminate\Support\Facades\DB::table('employees')->insert([
            'tenant_id' => $tenantId,
            'employee_code' => 'EMP-TEST-99',
            'first_name' => 'John',
            'last_name' => 'Mechanic',
            'phone' => '01822334455',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson('/api/v1/workshop/suggestions');
        $response->assertStatus(200);
        $this->assertArrayHasKey('mechanic_suggestions', $response->json('data'));
        $this->assertArrayHasKey('workshop_bay_suggestions', $response->json('data'));
        $this->assertArrayHasKey('complaint_templates', $response->json('data'));
        $this->assertArrayHasKey('suggested_parts', $response->json('data'));
    }

    public function test_customer_portal_analytics_and_segmentation()
    {
        // Create Super Admin role just in case
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'sanctum']);

        $uuid = (string) \Illuminate\Support\Str::uuid();

        // 1. Log events anonymously
        $response1 = $this->postJson('/api/v1/portal/analytics/log-event', [
            'uuid' => $uuid,
            'event_type' => 'quotation_view'
        ]);
        $response1->assertStatus(200);

        $response2 = $this->postJson('/api/v1/portal/analytics/log-event', [
            'uuid' => $uuid,
            'event_type' => 'item_expansion'
        ]);
        $response2->assertStatus(200);

        // 2. Query as Super Admin
        $user = \App\Models\User::first();
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        $response3 = $this->getJson('/api/v1/saas/portal-analytics');
        $response3->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response3->json('data.total_active_sessions'));
        $this->assertGreaterThanOrEqual(2, $response3->json('data.total_logged_actions'));
    }

    public function test_subscription_renewal_alerts()
    {
        $tenant = \App\Models\Tenant::create(['company_name' => 'Expiring Tenant Ltd', 'domain' => 'expire.erp']);
        
        // 1. Create a subscription expiring in 5 days
        \Illuminate\Support\Facades\DB::table('tenant_subscriptions')->insert([
            'tenant_id' => $tenant->id,
            'plan_name' => 'Enterprise Pro',
            'payment_gateway' => 'stripe',
            'status' => 'active',
            'starts_at' => now()->subDays(25),
            'ends_at' => now()->addDays(5),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Clear pre-existing reminder logs for clean test
        \Illuminate\Support\Facades\DB::table('audit_logs')
            ->where('tenant_id', $tenant->id)
            ->where('action', 'subscription_renewal_reminder')
            ->delete();

        // 2. Run reminder command
        $this->artisan('saas:send-renewal-reminders')
            ->assertExitCode(0);

        // Assert audit log exists
        $this->assertTrue(\Illuminate\Support\Facades\DB::table('audit_logs')
            ->where('tenant_id', $tenant->id)
            ->where('action', 'subscription_renewal_reminder')
            ->exists());

        // Assert system health alert exists
        $this->assertTrue(\Illuminate\Support\Facades\DB::table('system_health_alerts')
            ->where('alert_type', 'subscription_expiring')
            ->exists());

        // 3. Run again and check 24-hour duplicate prevention
        $logCountBefore = \Illuminate\Support\Facades\DB::table('audit_logs')
            ->where('tenant_id', $tenant->id)
            ->where('action', 'subscription_renewal_reminder')
            ->count();

        $this->artisan('saas:send-renewal-reminders');

        $logCountAfter = \Illuminate\Support\Facades\DB::table('audit_logs')
            ->where('tenant_id', $tenant->id)
            ->where('action', 'subscription_renewal_reminder')
            ->count();

        $this->assertEquals($logCountBefore, $logCountAfter); // No extra log sent due to 24h block

        // 4. Opt out / Unsubscribe and check skip
        \Illuminate\Support\Facades\Cache::put("tenant_unsubscribed_reminders_{$tenant->id}", true);
        
        // Remove logs to test send skip
        \Illuminate\Support\Facades\DB::table('audit_logs')
            ->where('tenant_id', $tenant->id)
            ->where('action', 'subscription_renewal_reminder')
            ->delete();

        $this->artisan('saas:send-renewal-reminders');

        $this->assertFalse(\Illuminate\Support\Facades\DB::table('audit_logs')
            ->where('tenant_id', $tenant->id)
            ->where('action', 'subscription_renewal_reminder')
            ->exists()); // Skipped sending due to opt-out
    }

    public function test_update_user_without_employee_profile_auto_generates_employee_code()
    {
        $admin = \App\Models\User::first();
        $this->actingAs($admin);

        // 1. Create a user who does NOT have an employee profile yet
        $user = \App\Models\User::create([
            'name' => 'John Technician',
            'email' => 'john.tech@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertFalse(\App\Models\Employee::where('user_id', $user->id)->exists());

        // 2. Perform PUT request to update user, without passing employee_code (leave it blank)
        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'name' => 'John Technician Updated',
            'email' => 'john.tech@example.com',
            'role' => 'Technician',
            'salary' => 25000,
            'employee_code' => '', // blank like in the frontend screen
        ]);

        $response->assertStatus(200);

        // 3. Assert that employee profile was created and employee_code was auto-generated
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        $this->assertNotNull($employee);
        $this->assertNotEmpty($employee->employee_code);
        $this->assertStringStartsWith('EMP-', $employee->employee_code);
        $this->assertEquals(25000, $employee->salary);
    }
}
