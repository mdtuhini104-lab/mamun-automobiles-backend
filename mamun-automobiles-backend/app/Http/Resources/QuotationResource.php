<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
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
            'quotation_number' => $this->quotation_number,
            'version' => (int) $this->version,
            'status' => $this->status,
            'total_product_cost' => (float) $this->total_product_cost,
            'total_labor_cost' => (float) $this->total_labor_cost,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'grand_total' => (float) $this->grand_total,
            'notes' => $this->notes,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            
            // Defensive nullable relation formats
            'job_card' => $this->whenLoaded('jobCard', function () {
                return $this->jobCard ? [
                    'id' => $this->jobCard->id,
                    'service_status' => $this->jobCard->service_status,
                ] : null;
            }),
            
            'items' => QuotationItemResource::collection($this->whenLoaded('items')),
            
            'removed_items' => RemovedQuotationItemResource::collection($this->whenLoaded('removedItems')),
            
            'approvals' => CustomerApprovalResource::collection($this->whenLoaded('approvals')),
            
            'created_by' => $this->whenLoaded('createdBy', function () {
                return $this->createdBy ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ] : null;
            }),
        ];
    }
}
