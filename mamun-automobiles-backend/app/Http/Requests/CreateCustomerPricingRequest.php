<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomerPricingRequest extends FormRequest
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
            'customer_id' => 'required|integer|exists:customers,id',
            'part_id' => 'nullable|integer|exists:parts,id',
            'labor_service_name' => 'nullable|string|max:255',
            'custom_price' => 'nullable|numeric|min:0',
            'custom_labor_rate' => 'nullable|numeric|min:0',
            'effective_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
