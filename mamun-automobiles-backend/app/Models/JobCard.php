<?php

namespace App\Models;

use App\Traits\MultitenantSafe;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ServiceStatus;

class JobCard extends Model
{
    use SoftDeletes, MultitenantSafe, BranchScoped;
    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'assigned_mechanic_id',
        'complaint',
        'diagnosis',
        'service_status',
        'estimated_cost',
        'final_cost',
        'service_date',
        'start_date',
        'delivery_date',
        'notes',
        'fuel_level',
        'odometer_reading',
        'emergency_level',
        'expected_delivery_date',
        'inspection_notes',
        'priority_level',
        'safety_warnings',
        'voice_notes_path',
        'images_paths',
        'documents_paths',
        'ai_priority_score',
        'ai_failure_probability',
    ];

    protected $casts = [
        'service_status' => ServiceStatus::class,
        'expected_delivery_date' => 'datetime',
        'images_paths' => 'array',
        'documents_paths' => 'array',
        'ai_priority_score' => 'float',
        'ai_failure_probability' => 'float',
    ];

    /**
     * Get the customer associated with the job card.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the vehicle associated with the job card.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the mechanic assigned to the job card.
     */
    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_mechanic_id');
    }

    /**
     * Get the items (parts) for the job card.
     */
    public function items(): HasMany
    {
        return $this->hasMany(JobCardItem::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function workshopBay(): BelongsTo
    {
        return $this->belongsTo(WorkshopBay::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(JobCardAssignment::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(JobCardTask::class);
    }

    /**
     * Get all quotations associated with this job card.
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Get the work order generated from this job card.
     */
    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    /**
     * Get the workflow state history logs.
     */
    public function workflowHistories(): HasMany
    {
        return $this->hasMany(WorkflowHistory::class);
    }
}

