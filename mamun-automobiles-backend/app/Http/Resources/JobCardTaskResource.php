<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobCardTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_card_id' => $this->job_card_id,
            'name' => $this->name,
            'description' => $this->description,
            'estimated_minutes' => $this->estimated_minutes,
            'status' => $this->status,
            'assignments' => JobTaskAssignmentResource::collection($this->whenLoaded('taskAssignments')),
        ];
    }
}
