<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
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
        $vehicleId = $this->route('id');

        return [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'license_plate' => 'sometimes|required|string|max:20|unique:vehicles,license_plate,' . $vehicleId,
            'make' => 'sometimes|required|string|max:255',
            'model' => 'sometimes|required|string|max:255',
            'year' => 'nullable|integer',
            'vin' => 'nullable|string|max:255',
            'engine_number' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'fuel_type' => 'nullable|string|max:50',
        ];
    }
}
