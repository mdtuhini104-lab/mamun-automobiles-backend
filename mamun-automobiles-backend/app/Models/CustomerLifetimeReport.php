<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLifetimeReport extends Model
{
    protected $fillable = [
        'customer_id',
        'total_invoices',
        'total_revenue',
        'avg_invoice_value',
        'last_active_at',
    ];

    protected $casts = [
        'total_invoices' => 'integer',
        'total_revenue' => 'float',
        'avg_invoice_value' => 'float',
        'last_active_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
