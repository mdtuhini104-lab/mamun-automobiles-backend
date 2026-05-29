<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RemovedQuotationItemResource extends JsonResource
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
            'item_name' => $this->item_name,
            'removed_by_id' => $this->removed_by_id,
            'removal_reason' => $this->removal_reason,
            'previous_quantity' => (float) $this->previous_quantity,
            'previous_price' => (float) $this->previous_price,
            'removed_at' => $this->removed_at ? $this->removed_at->toIso8601String() : null,
            
            'removed_by' => $this->whenLoaded('removedBy', function () {
                return $this->removedBy ? [
                    'id' => $this->removedBy->id,
                    'name' => $this->removedBy->name,
                ] : null;
            }),
        ];
    }
}
