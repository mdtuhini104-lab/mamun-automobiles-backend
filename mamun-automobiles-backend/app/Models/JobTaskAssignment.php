<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class JobTaskAssignment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'job_card_task_id',
        'employee_id',
        'allocated_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'allocated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(JobCardTask::class, 'job_card_task_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
