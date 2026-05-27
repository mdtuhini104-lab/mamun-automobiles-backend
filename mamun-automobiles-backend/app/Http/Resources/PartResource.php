<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use Milon\Barcode\Facades\DNS2DFacade as DNS2D;

class PartResource extends JsonResource
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
            'name' => $this->name,
            'sku' => $this->sku,
            'brand' => $this->brand,
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'stock_quantity' => $this->stock_quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'barcode_value' => $this->barcode,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'rack_location' => $this->rack_location,
            'unit_type' => $this->unit_type,
            'barcode' => $this->sku ? DNS1D::getBarcodeSVG($this->sku, 'C128') : null,
            'qrcode' => $this->sku ? DNS2D::getBarcodeSVG($this->sku, 'QRCODE') : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
