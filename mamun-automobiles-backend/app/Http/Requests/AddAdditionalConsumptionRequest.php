<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddAdditionalConsumptionRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'item_type' => 'required|string|in:product,service',
            'part_id' => 'nullable|integer|exists:parts,id|required_if:item_type,product',
            'service_name' => 'nullable|string|max:255|required_if:item_type,service',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'source_type' => 'nullable|string|in:workshop_supplied,customer_supplied',
            'notes' => 'nullable|string|max:1000',
            'is_approved' => 'nullable|boolean',
        ];
    }
}
