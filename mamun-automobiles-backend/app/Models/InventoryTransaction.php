<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'part_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'unit_cost',
        'notes',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
