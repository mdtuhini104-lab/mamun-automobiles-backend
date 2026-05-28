<?php

namespace App\Repositories;

use App\Models\EmployeeAvailability;

class EmployeeAvailabilityRepository extends BaseRepository
{
    public function __construct(EmployeeAvailability $model)
    {
        parent::__construct($model);
    }

    public function create(array $data): EmployeeAvailability
    {
        return EmployeeAvailability::create($data);
    }

    public function getByEmployeeId(int $employeeId)
    {
        return EmployeeAvailability::where('employee_id', $employeeId)->latest()->get();
    }
}
