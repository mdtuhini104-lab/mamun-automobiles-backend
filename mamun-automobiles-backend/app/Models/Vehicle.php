<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'customer_id',
        'license_plate',
        'make',
        'model',
        'year',
        'vin',
        'engine_number',
        'color',
        'fuel_type',
    ];

    /**
     * Get the customer that owns the vehicle.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the job cards for the vehicle.
     */
    public function jobCards(): HasMany
    {
        return $this->hasMany(JobCard::class);
    }
}
