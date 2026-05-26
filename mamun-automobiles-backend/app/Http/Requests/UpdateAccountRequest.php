<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:cash,bank',
            'account_no' => 'nullable|string|max:255',
            'balance' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|string|in:active,inactive',
        ];
    }
}
