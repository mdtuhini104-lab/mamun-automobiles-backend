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
            'email' => 'admin@admin.com',
            'password' => 'password',
        ]);
        
        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response->json());
    }

    public function test_transaction_rollback_on_failed_job_card()
    {
        $user = \App\Models\User::first();
        $this->actingAs($user);

        // Try to create job card item with insufficient stock, should fail and rollback
        $jobCard = \App\Models\JobCard::first();
        $part = \App\Models\Part::first();
        
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
}
