<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone', 'address', 'nid', 'salary', 'joining_date', 'is_active', 'department_id', 'designation_id', 'shift_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    public const ROLE_SUPER_ADMIN = 'Super Admin';
    public const ROLE_MANAGER = 'Manager';
    public const ROLE_TECHNICIAN = 'Technician';
    public const ROLE_CASHIER = 'Cashier';
    public const ROLE_STORE_MANAGER = 'Store Manager';

    protected string $guard_name = 'web';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'salary' => 'float',
            'joining_date' => 'date:Y-m-d',
            'is_active' => 'boolean',
        ];
    }

    public function assignedJobs()
    {
        return $this->hasMany(\App\Models\JobCard::class, 'assigned_mechanic_id');
    }

    public function employee()
    {
        return $this->hasOne(\App\Models\Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(\App\Models\Designation::class);
    }
}
