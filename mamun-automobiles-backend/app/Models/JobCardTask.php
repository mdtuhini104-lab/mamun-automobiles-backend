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

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(JobTaskAssignment::class);
    }
}
