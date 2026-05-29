<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class VehicleDelivery extends Model
{
    use LogsActivity;

    protected $fillable = [
        'job_card_id',
        'delivered_to',
        'signature_path',
        'delivery_photos',
        'delivered_by_id',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'delivery_photos' => 'array',
        'delivered_at' => 'datetime',
    ];

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by_id');
    }
}
