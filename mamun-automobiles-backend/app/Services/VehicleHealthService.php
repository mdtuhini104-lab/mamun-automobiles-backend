<?php

namespace App\Services;

class VehicleHealthService
{
    public function calculateHealthScore($vehicleId)
    {
        return [
            'score' => 82,
            'status' => 'Good',
            'next_service_prediction' => '2023-12-01'
        ];
    }
}
