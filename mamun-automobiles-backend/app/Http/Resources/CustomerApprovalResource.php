<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerApprovalResource extends JsonResource
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
            'status' => $this->status,
            'approved_by' => $this->approved_by,
            'user_id' => $this->user_id,
            'signature_path' => $this->signature_path,
            'notes' => $this->notes,
            'approved_items' => $this->approved_items,
            'rejected_items' => $this->rejected_items,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ] : null;
            }),
        ];
    }
}
