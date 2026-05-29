<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class QualityControl extends Model
{
    use LogsActivity;

    protected $fillable = [
        'work_order_id',
        'status',
        'supervisor_id',
        'checklist',
        'road_test_performed',
        'road_test_notes',
        'inspected_at',
    ];

    protected $casts = [
        'checklist' => 'array',
        'road_test_performed' => 'boolean',
        'inspected_at' => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
