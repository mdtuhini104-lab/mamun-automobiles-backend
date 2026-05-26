<?php

namespace App\Services;

use App\Models\MechanicAssignment;

class MechanicAssignmentService
{
    public function assignMechanic($jobCardId, $mechanicId, $assignedBy, $estimatedHours = null)
    {
        return MechanicAssignment::create([
            'job_card_id' => $jobCardId,
            'mechanic_id' => $mechanicId,
            'assigned_by' => $assignedBy,
            'estimated_hours' => $estimatedHours,
            'status' => 'pending'
        ]);
    }
}
