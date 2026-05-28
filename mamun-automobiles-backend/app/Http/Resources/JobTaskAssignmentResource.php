<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobTaskAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_card_task_id' => $this->job_card_task_id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return $this->employee ? [
                    'id' => $this->employee->id,
                    'first_name' => $this->employee->first_name,
                    'last_name' => $this->employee->last_name,
                    'employee_code' => $this->employee->employee_code,
                ] : null;
            }),
            'allocated_at' => $this->allocated_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'status' => $this->status,
        ];
    }
}
