<?php

namespace App\Models;

use App\Traits\MultitenantSafe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportTicket extends Model
{
    use HasFactory, MultitenantSafe;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'description',
        'priority',
        'category',
        'assigned_to',
        'status',
        'first_response_at',
        'resolved_at',
        'satisfaction_score'
    ];

    protected $casts = [
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'satisfaction_score' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function incident(): HasOne
    {
        return $this->hasOne(SupportIncident::class, 'ticket_id');
    }
}
