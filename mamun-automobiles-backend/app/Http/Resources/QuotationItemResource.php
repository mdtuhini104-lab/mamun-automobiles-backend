<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationItemResource extends JsonResource
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
            'quotation_id' => $this->quotation_id,
            'item_type' => $this->item_type,
            'part_id' => $this->part_id,
            'service_name' => $this->service_name,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'labor_cost' => (float) $this->labor_cost,
            'estimated_hours' => (float) $this->estimated_hours,
            'source_type' => $this->source_type,
            'status' => $this->status,
            'ai_price_recommendation' => (float) $this->ai_price_recommendation,
            
            // Defensive nullable eager-loading
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
