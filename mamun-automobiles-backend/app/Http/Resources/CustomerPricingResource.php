<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerPricingResource extends JsonResource
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
            'customer_id' => $this->customer_id,
            'part_id' => $this->part_id,
            'labor_service_name' => $this->labor_service_name,
            'custom_price' => (float) $this->custom_price,
            'custom_labor_rate' => (float) $this->custom_labor_rate,
            'effective_date' => $this->effective_date ? $this->effective_date->toDateString() : null,
            'notes' => $this->notes,
            
            // Relations
            'customer' => $this->whenLoaded('customer', function () {
                return $this->customer ? [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                ] : null;
            }),
            
            'part' => $this->whenLoaded('part', function () {
                return $this->part ? [
                    'id' => $this->part->id,
                    'name' => $this->part->name,
                    'sku' => $this->part->sku,
                    'sale_price' => (float) $this->part->sale_price,
                ] : null;
            }),
        ];
    }
}
