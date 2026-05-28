<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopBayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'branch_id' => $this->branch_id,
            'max_vehicle_capacity' => $this->max_vehicle_capacity,
            'current_load' => $this->current_load,
            'status' => $this->status,
        ];
    }
}
