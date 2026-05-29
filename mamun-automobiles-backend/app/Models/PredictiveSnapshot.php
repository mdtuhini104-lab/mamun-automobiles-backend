<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictiveSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'bay_utilization',
        'technician_loads',
        'queue_backlog',
        'delay_counts',
        'active_tasks',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'bay_utilization' => 'float',
        'technician_loads' => 'array',
        'queue_backlog' => 'integer',
        'delay_counts' => 'array',
        'active_tasks' => 'integer',
    ];
}
