<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'nid' => $this->nid,
            'salary' => $this->salary,
            'joining_date' => $this->joining_date,
            'is_active' => (bool) $this->is_active,
            'department_id' => $this->department_id,
            'designation_id' => $this->designation_id,
            'shift_id' => $this->shift_id,
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'employee' => $this->relationLoaded('employee') && $this->employee ? [
                'id' => $this->employee->id,
                'employee_code' => $this->employee->employee_code,
                'status' => $this->employee->status,
                'availability_status' => $this->employee->availability_status,
                'department' => $this->employee->relationLoaded('department') && $this->employee->department ? [
                    'id' => $this->employee->department->id,
                    'name' => $this->employee->department->name,
                    'code' => $this->employee->department->code,
                ] : null,
                'designation' => $this->employee->relationLoaded('designation') && $this->employee->designation ? [
                    'id' => $this->employee->designation->id,
                    'name' => $this->employee->designation->name,
                    'code' => $this->employee->designation->code,
                ] : null,
                'skills' => $this->employee->relationLoaded('skills') ? $this->employee->skills->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'code' => $s->code,
                        'proficiency_level' => $s->pivot?->proficiency_level,
                    ];
                }) : [],
            ] : null,
            'stats' => $this->when($this->hasRole(\App\Models\User::ROLE_TECHNICIAN), function () {
                return [
                    'active_jobs' => $this->assignedJobs()->where('service_status', '!=', 'completed')->count(),
                    'completed_jobs' => $this->assignedJobs()->where('service_status', 'completed')->count(),
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
