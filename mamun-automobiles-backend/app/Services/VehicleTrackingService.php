<?php

namespace App\Services;

class VehicleTrackingService
{
    public function getLiveStatus($jobCardId)
    {
        return [
            'status' => 'Repair Running',
            'progress' => 65,
            'estimated_completion' => 'Tomorrow 5:00 PM',
            'assigned_mechanic' => 'Rahim',
            'timeline' => [
                ['status' => 'Vehicle Received', 'time' => '10:00 AM', 'completed' => true],
                ['status' => 'Inspection Running', 'time' => '11:00 AM', 'completed' => true],
                ['status' => 'Repair Running', 'time' => '1:00 PM', 'completed' => false]
            ]
        ];
    }
}
