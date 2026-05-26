<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:cash,bank',
            'account_no' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
