<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class JobCardTask extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'job_card_id',
        'name',
        'description',
        'estimated_minutes',
        'status',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
    ];

    protected $appends = [
        'actual_minutes',
    ];

    public function getActualMinutesAttribute(): int
    {
        $total = 0;
        // Make sure taskAssignments relation is loaded, or query it if not
        $assignments = $this->relationLoaded('taskAssignments') ? $this->taskAssignments : $this->taskAssignments()->get();
        foreach ($assignments as $assignment) {
            $start = $assignment->allocated_at;
            $end = $assignment->completed_at ?? ($assignment->status === 'active' ? now() : null);
            if ($start && $end) {
                $total += $start->diffInMinutes($end);
            }
        }
        return $total;
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(JobTaskAssignment::class);
    }
}
