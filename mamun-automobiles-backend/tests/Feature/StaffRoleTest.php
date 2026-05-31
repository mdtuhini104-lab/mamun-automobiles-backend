<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class StaffRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    /**
     * Test that Super Admin can access all administrative and telemetry routes.
     */
    public function test_super_admin_has_full_telemetry_access()
    {
        $superAdmin = User::where('email', 'admin@mamunerp.com')->first();
        $this->assertNotNull($superAdmin, 'Super Admin user must exist.');
        $this->actingAs($superAdmin);

        $response = $this->getJson('/api/v1/system/performance/telemetry');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'slow_queries',
                'queue_latency',
                'cache_telemetry'
            ]
        ]);
    }

    /**
     * Test that Super Admin can toggle maintenance mode.
     */
    public function test_super_admin_can_toggle_maintenance()
    {
        $superAdmin = User::where('email', 'admin@mamunerp.com')->first();
        $this->actingAs($superAdmin);

        $response = $this->postJson('/api/v1/system/maintenance', [
            'enabled' => true
        ]);
        $response->assertStatus(200);
        $this->assertTrue($response->json('maintenance_mode'));
    }

    /**
     * Test that Manager cannot toggle system maintenance mode (403 Forbidden).
     */
    public function test_manager_cannot_toggle_maintenance()
    {
        $manager = User::where('email', 'manager@mamunerp.com')->first();
        $this->assertNotNull($manager, 'Manager user must exist.');
        $this->actingAs($manager);

        $response = $this->postJson('/api/v1/system/maintenance', [
            'enabled' => true
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test that Technician can fetch mobile tasks, but is blocked from clearing failed jobs.
     */
    public function test_technician_can_access_mobile_tasks_but_blocked_from_failed_jobs()
    {
        $technician = User::where('email', 'tech@mamunerp.com')->first();
        $this->assertNotNull($technician, 'Technician user must exist.');
        $this->actingAs($technician);

        // 1. Can access mobile tasks
        $responseTasks = $this->getJson('/api/v1/mobile/tasks');
        $responseTasks->assertStatus(200);

        // 2. Blocked from administrative failed jobs clear
        $responseClear = $this->postJson('/api/v1/system/failed-jobs/clear');
        $responseClear->assertStatus(403);
    }

    /**
     * Test that Cashier cannot access performance telemetry.
     */
    public function test_cashier_blocked_from_telemetry()
    {
        $cashier = User::where('email', 'cashier@mamunerp.com')->first();
        $this->assertNotNull($cashier, 'Cashier user must exist.');
        $this->actingAs($cashier);

        $response = $this->getJson('/api/v1/system/performance/telemetry');
        $response->assertStatus(403);
    }
}
