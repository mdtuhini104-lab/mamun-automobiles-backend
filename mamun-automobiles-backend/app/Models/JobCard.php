<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ServiceStatus;

class JobCard extends Model
{
    use SoftDeletes;
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
    ];

    protected $casts = [
        'service_status' => ServiceStatus::class,
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
}
