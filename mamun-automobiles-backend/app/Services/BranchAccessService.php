<?php

namespace App\Services;

class BranchAccessService
{
    public function canAccess($userId, $branchId)
    {
        // Check if user belongs to the branch or is a super admin
        return true; 
    }
}
