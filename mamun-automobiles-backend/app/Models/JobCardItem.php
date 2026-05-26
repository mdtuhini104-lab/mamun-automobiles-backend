<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCardItem extends Model
{
    protected $fillable = [
        'job_card_id',
        'part_id',
        'quantity',
        'unit_price',
    ];

    /**
     * Get the job card associated with the item.
     */
    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    /**
     * Get the part associated with the item.
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
