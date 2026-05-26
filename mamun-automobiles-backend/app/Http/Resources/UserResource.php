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
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'stats' => $this->when($this->hasRole('Mechanic'), function () {
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
