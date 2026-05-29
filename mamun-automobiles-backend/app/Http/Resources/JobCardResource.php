<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'mechanic' => $this->whenLoaded('mechanic', function () {
                return $this->mechanic ? [
                    'id' => $this->mechanic->id,
                    'name' => $this->mechanic->name,
                ] : null;
            }),
            'complaint' => $this->complaint,
            'diagnosis' => $this->diagnosis,
            'service_status' => $this->service_status,
            'estimated_cost' => $this->estimated_cost,
            'final_cost' => $this->final_cost,
            'service_date' => $this->service_date,
            'start_date' => $this->start_date,
            'delivery_date' => $this->delivery_date,
            'notes' => $this->notes,
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', function () {
                return $this->department ? [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                    'code' => $this->department->code,
                ] : null;
            }),
            'workshop_bay_id' => $this->workshop_bay_id,
            'workshop_bay' => new WorkshopBayResource($this->whenLoaded('workshopBay')),
            'assignments' => JobCardAssignmentResource::collection($this->whenLoaded('assignments')),
            'tasks' => JobCardTaskResource::collection($this->whenLoaded('tasks')),
            'workflow_history' => WorkflowHistoryResource::collection($this->whenLoaded('workflowHistory')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
