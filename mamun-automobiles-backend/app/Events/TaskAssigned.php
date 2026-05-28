<?php

namespace App\Events;

use App\Models\JobTaskAssignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned
{
    use Dispatchable, SerializesModels;

    public $taskAssignment;

    public function __construct(JobTaskAssignment $taskAssignment)
    {
        $this->taskAssignment = $taskAssignment;
    }
}
