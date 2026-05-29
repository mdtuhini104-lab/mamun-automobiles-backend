<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class WorkOrderConsumption extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'work_order_id',
        'item_type',
        'part_id',
        'service_name',
        'quantity',
        'actual_consumed_quantity',
        'wasted_quantity',
        'returned_quantity',
        'unit_price',
        'source_type',
        'consumed_by_id',
        'notes',
        'is_approved',
        'approved_by_id',
    ];

    protected $casts = [
        'quantity' => 'float',
        'actual_consumed_quantity' => 'float',
        'wasted_quantity' => 'float',
        'returned_quantity' => 'float',
        'unit_price' => 'float',
        'is_approved' => 'boolean',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function consumedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumed_by_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
