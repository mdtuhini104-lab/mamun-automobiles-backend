<?php

namespace App\Services;

class BranchService
{
    public function getAllBranches()
    {
        // This would interact with the database to get all branches
        // For demonstration, returning a dummy list
        return [
            ['id' => 1, 'name' => 'Main Branch', 'location' => 'Dhaka'],
            ['id' => 2, 'name' => 'Chittagong Branch', 'location' => 'Chittagong']
        ];
    }
}
