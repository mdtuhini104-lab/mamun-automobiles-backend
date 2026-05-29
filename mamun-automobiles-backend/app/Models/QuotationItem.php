<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class QuotationItem extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'quotation_id',
        'item_type',
        'part_id',
        'part_name_snapshot',
        'sku_snapshot',
        'customer_name_snapshot',
        'service_name',
        'quantity',
        'unit_price',
        'price_snapshot',
        'discount',
        'tax',
        'tax_snapshot',
        'labor_cost',
        'labor_snapshot',
        'estimated_hours',
        'source_type',
        'status',
        'ai_price_recommendation',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'float',
        'price_snapshot' => 'float',
        'discount' => 'float',
        'tax' => 'float',
        'tax_snapshot' => 'float',
        'labor_cost' => 'float',
        'labor_snapshot' => 'float',
        'estimated_hours' => 'float',
        'ai_price_recommendation' => 'float',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
