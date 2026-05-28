<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobCardAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_card_id' => $this->job_card_id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return $this->employee ? [
                    'id' => $this->employee->id,
                    'first_name' => $this->employee->first_name,
                    'last_name' => $this->employee->last_name,
                    'employee_code' => $this->employee->employee_code,
                ] : null;
            }),
            'assignment_type' => $this->assignment_type,
            'started_at' => $this->started_at?->toIso8601String(),
            'ended_at' => $this->ended_at?->toIso8601String(),
            'labor_hours' => $this->labor_hours,
            'status' => $this->status,
        ];
    }
}
