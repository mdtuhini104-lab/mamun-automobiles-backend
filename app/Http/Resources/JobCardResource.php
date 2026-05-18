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
                return [
                    'id' => $this->mechanic->id,
                    'name' => $this->mechanic->name,
                ];
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
