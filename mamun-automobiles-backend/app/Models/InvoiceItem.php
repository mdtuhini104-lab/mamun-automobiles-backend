<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'part_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * Get the invoice associated with the item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the part associated with the item.
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
