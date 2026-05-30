<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');
        $employee = \App\Models\Employee::where('user_id', $userId)->first();
        $employeeId = $employee ? $employee->id : '';

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'password' => 'sometimes|nullable|string|min:6',
            'role' => 'sometimes|string|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'nid' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'department_id' => 'nullable|integer|exists:departments,id',
            'designation_id' => 'nullable|integer|exists:designations,id',
            'shift_id' => 'nullable|integer|exists:shifts,id',
            'employee_code' => 'nullable|string|max:50|unique:employees,employee_code,' . $employeeId,
            'status' => 'nullable|string',
            'availability_status' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ];
    }
}
