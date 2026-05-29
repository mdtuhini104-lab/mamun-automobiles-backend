<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\LogsActivity;
use App\Traits\MultitenantSafe;
use App\Traits\BranchScoped;

class Quotation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, MultitenantSafe, BranchScoped;

    protected $fillable = [
        'job_card_id',
        'quotation_number',
        'version',
        'status',
        'total_product_cost',
        'total_labor_cost',
        'discount',
        'tax',
        'grand_total',
        'created_by',
        'notes',
        'ai_priority_score',
        'ai_customer_acceptance_probability',
        'ai_estimated_completion_hours',
        'ai_technician_efficiency_score',
        'ai_inventory_prediction_score',
    ];

    protected $casts = [
        'version' => 'integer',
        'total_product_cost' => 'float',
        'total_labor_cost' => 'float',
        'discount' => 'float',
        'tax' => 'float',
        'grand_total' => 'float',
        'ai_priority_score' => 'float',
        'ai_customer_acceptance_probability' => 'float',
        'ai_estimated_completion_hours' => 'float',
        'ai_technician_efficiency_score' => 'float',
        'ai_inventory_prediction_score' => 'float',
    ];

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function removedItems(): HasMany
    {
        return $this->hasMany(RemovedQuotationItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(CustomerApproval::class);
    }

    public function latestApproval(): HasOne
    {
        return $this->hasOne(CustomerApproval::class)->latestOfMany();
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(QuotationVersionSnapshot::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
