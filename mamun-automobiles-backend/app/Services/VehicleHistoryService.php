<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class VehicleHistoryService
{
    public function recordHistory($vehicleId, $customerId, $jobCardId, $invoiceId, $data)
    {
        $existing = DB::table('vehicle_service_histories')
            ->where('job_card_id', $jobCardId)
            ->first();

        if ($existing) {
            return false; // Prevent duplicate
        }

        DB::table('vehicle_service_histories')->insert([
            'vehicle_id' => $vehicleId,
            'customer_id' => $customerId,
            'job_card_id' => $jobCardId,
            'invoice_id' => $invoiceId,
            'service_date' => $data['service_date'] ?? now()->toDateString(),
            'mileage' => $data['mileage'] ?? null,
            'complaints' => $data['complaints'] ?? null,
            'services_done' => $data['services_done'] ?? null,
            'parts_changed' => $data['parts_changed'] ?? null,
            'mechanic_name' => $data['mechanic_name'] ?? null,
            'total_cost' => $data['total_cost'] ?? 0,
            'status' => 'completed',
            'next_service_date' => $data['next_service_date'] ?? now()->addMonths(3)->toDateString(),
            'notes' => $data['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logActivity('Vehicle History Created', "Job Card #$jobCardId for Vehicle #$vehicleId");

        return true;
    }
    
    private function logActivity($action, $note)
    {
        try {
            DB::table('activity_logs')->insert([
                'user_id' => auth()->id() ?? 1,
                'action' => $action,
                'description' => $note,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {}
    }
}
