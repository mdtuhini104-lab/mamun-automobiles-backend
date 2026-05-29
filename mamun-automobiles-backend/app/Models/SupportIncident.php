<?php

namespace App\Models;

use App\Traits\MultitenantSafe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportIncident extends Model
{
    use HasFactory, MultitenantSafe;

    protected $fillable = [
        'tenant_id',
        'ticket_id',
        'title',
        'description',
        'status',
        'severity'
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function workflow(): HasOne
    {
        return $this->hasOne(ResolutionWorkflow::class, 'incident_id');
    }
}
