<?php

namespace App\Events;

use App\Models\Employee;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AvailabilityChanged
{
    use Dispatchable, SerializesModels;

    public $employee;
    public $status;

    public function __construct(Employee $employee, string $status)
    {
        $this->employee = $employee;
        $this->status = $status;
    }
}
