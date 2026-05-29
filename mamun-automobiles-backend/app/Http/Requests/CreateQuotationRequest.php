<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Spatie RBAC handled in controller authorization
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'job_card_id' => 'required|integer|exists:job_cards,id',
            'notes' => 'nullable|string|max:1000',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|string|in:product,service',
            'items.*.part_id' => 'nullable|integer|exists:parts,id|required_if:items.*.item_type,product',
            'items.*.service_name' => 'nullable|string|max:255|required_if:items.*.item_type,service',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.labor_cost' => 'nullable|numeric|min:0',
            'items.*.estimated_hours' => 'nullable|numeric|min:0',
            'items.*.source_type' => 'nullable|string|in:workshop_supplied,customer_supplied',
            'items.*.ai_price_recommendation' => 'nullable|numeric|min:0',
        ];
    }
}
