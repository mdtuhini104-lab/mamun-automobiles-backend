<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class JobCardAssignment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'job_card_id',
        'employee_id',
        'assignment_type',
        'started_at',
        'ended_at',
        'labor_hours',
        'status',
        'reassigned_to_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'labor_hours' => 'float',
    ];

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reassignedTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reassigned_to_id');
    }
}
