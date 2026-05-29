<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class RemovedQuotationItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'quotation_id',
        'item_name',
        'removed_by_id',
        'removal_reason',
        'previous_quantity',
        'previous_price',
        'removed_at',
    ];

    protected $casts = [
        'previous_quantity' => 'float',
        'previous_price' => 'float',
        'removed_at' => 'datetime',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by_id');
    }
}
