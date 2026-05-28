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
}
