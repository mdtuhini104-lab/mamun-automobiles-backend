<?php

namespace App\Services;

class MechanicMobileService
{
    public function updateJobProgress($jobId, $status, $mechanicId, $notes = null)
    {
        // Mobile specific mechanic updates
        return true;
    }
}
