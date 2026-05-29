<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
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
            'job_card_id' => $this->job_card_id,
            'quotation_id' => $this->quotation_id,
            'work_order_number' => $this->work_order_number,
            'status' => $this->status,
            'department_allocations' => $this->department_allocations,
            'started_at' => $this->started_at ? $this->started_at->toIso8601String() : null,
            'completed_at' => $this->completed_at ? $this->completed_at->toIso8601String() : null,
            'notes' => $this->notes,
            
            // AI hooks
            'ai_estimated_completion_hours' => (float) $this->ai_estimated_completion_hours,
            'ai_efficiency_score' => (float) $this->ai_efficiency_score,
            
            // Relations
            'job_card' => $this->whenLoaded('jobCard', function () {
                return $this->jobCard ? [
                    'id' => $this->jobCard->id,
                    'complaint' => $this->jobCard->complaint,
                    'customer_id' => $this->jobCard->customer_id,
                    'vehicle_id' => $this->jobCard->vehicle_id,
                ] : null;
            }),
            
            'quotation' => $this->whenLoaded('quotation', function () {
                return $this->quotation ? [
                    'id' => $this->quotation->id,
                    'quotation_number' => $this->quotation->quotation_number,
                    'version' => $this->quotation->version,
                ] : null;
            }),
        ];
    }
}
