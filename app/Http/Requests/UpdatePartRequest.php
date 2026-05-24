<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $partId = $this->route('id');
        
        return [
            'name' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:50|unique:parts,sku,' . $partId,
            'barcode' => 'nullable|string|max:100|unique:parts,barcode,' . $partId,
            'brand' => 'nullable|string|max:100',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'rack_location' => 'nullable|string|max:100',
            'unit_type' => 'nullable|string|max:50',
        ];
    }
}
