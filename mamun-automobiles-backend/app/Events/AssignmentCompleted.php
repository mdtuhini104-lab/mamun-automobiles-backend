<?php

namespace App\Events;

use App\Models\JobCardAssignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentCompleted
{
    use Dispatchable, SerializesModels;

    public $assignment;

    public function __construct(JobCardAssignment $assignment)
    {
        $this->assignment = $assignment;
    }
}
