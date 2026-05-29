<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class CustomerPricing extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'customer_pricings';

    protected $fillable = [
        'customer_id',
        'part_id',
        'labor_service_name',
        'custom_price',
        'custom_labor_rate',
        'effective_date',
        'notes',
    ];

    protected $casts = [
        'custom_price' => 'float',
        'custom_labor_rate' => 'float',
        'effective_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
