<?php

namespace App\Models;

use App\Traits\MultitenantSafe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantHealthSnapshot extends Model
{
    use HasFactory, MultitenantSafe;

    public $timestamps = false; // Using recorded_at timestamp only

    protected $fillable = [
        'tenant_id',
        'health_score',
        'days_inactive',
        'risk_level',
        'metrics',
        'recorded_at',
    ];

    protected $casts = [
        'metrics' => 'array',
        'recorded_at' => 'datetime',
        'health_score' => 'float',
        'days_inactive' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
