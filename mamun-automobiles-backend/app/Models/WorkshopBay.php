<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;
use App\Traits\MultitenantSafe;
use App\Traits\BranchScoped;

class WorkshopBay extends Model
{
    use HasFactory, LogsActivity, MultitenantSafe, BranchScoped;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'code',
        'max_vehicle_capacity',
        'current_load',
        'status',
    ];

    protected $casts = [
        'max_vehicle_capacity' => 'integer',
        'current_load' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function jobCards(): HasMany
    {
        return $this->hasMany(JobCard::class);
    }
}
