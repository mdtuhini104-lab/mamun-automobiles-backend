<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Employee extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'department_id',
        'designation_id',
        'branch_id',
        'employee_code',
        'first_name',
        'last_name',
        'phone',
        'address',
        'nid',
        'salary',
        'joining_date',
        'status',
        'availability_status',
    ];

    protected $casts = [
        'salary' => 'float',
        'joining_date' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'employee_skills')
            ->withPivot('proficiency_level');
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(EmployeeAvailability::class);
    }

    public function jobCardAssignments(): HasMany
    {
        return $this->hasMany(JobCardAssignment::class);
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(JobTaskAssignment::class);
    }
}
