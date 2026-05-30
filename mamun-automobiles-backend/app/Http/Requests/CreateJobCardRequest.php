<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateJobCardRequest extends FormRequest
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
        return [
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'assigned_mechanic_id' => 'nullable|exists:users,id',
            'complaint' => 'required|string',
            'diagnosis' => 'nullable|string',
            'service_status' => ['nullable', Rule::enum(\App\Enums\ServiceStatus::class)],
            'estimated_cost' => 'nullable|numeric|min:0',
            'final_cost' => 'nullable|numeric|min:0',
            'service_date' => 'required|date',
            'start_date' => 'nullable|date',
            'delivery_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'fuel_level' => 'nullable|string|max:50',
            'odometer_reading' => 'nullable|integer|min:0',
            'emergency_level' => 'nullable|string|in:low,medium,high,critical',
            'expected_delivery_date' => 'nullable|date',
            'inspection_notes' => 'nullable|string',
            'priority_level' => 'nullable|string|in:normal,high,urgent',
            'safety_warnings' => 'nullable|string',
            'images_paths' => 'nullable|array',
            'documents_paths' => 'nullable|array',
        ];
    }
}
