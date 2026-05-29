<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;
use App\Traits\MultitenantSafe;
use App\Traits\BranchScoped;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, MultitenantSafe, BranchScoped;

    protected $fillable = [
        'job_card_id',
        'quotation_id',
        'work_order_number',
        'status',
        'department_allocations',
        'started_at',
        'completed_at',
        'notes',
        'ai_estimated_completion_hours',
        'ai_efficiency_score',
        'ai_priority_score',
        'ai_customer_acceptance_probability',
        'ai_technician_efficiency_score',
        'ai_inventory_prediction_score',
    ];

    protected $casts = [
        'department_allocations' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'ai_estimated_completion_hours' => 'float',
        'ai_efficiency_score' => 'float',
        'ai_priority_score' => 'float',
        'ai_customer_acceptance_probability' => 'float',
        'ai_technician_efficiency_score' => 'float',
        'ai_inventory_prediction_score' => 'float',
    ];

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(WorkOrderConsumption::class);
    }
}
