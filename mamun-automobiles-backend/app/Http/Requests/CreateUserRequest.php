<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'nid' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'department_id' => 'nullable|integer|exists:departments,id',
            'designation_id' => 'nullable|integer|exists:designations,id',
            'shift_id' => 'nullable|integer|exists:shifts,id',
            'employee_code' => 'nullable|string|max:50|unique:employees,employee_code',
            'status' => 'nullable|string',
            'availability_status' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ];
    }
}
