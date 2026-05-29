<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class CustomerApproval extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'quotation_id',
        'status',
        'approved_by',
        'user_id',
        'signature_path',
        'notes',
        'approved_items',
        'rejected_items',
        'approval_stage',
        'approval_order',
        'approval_type',
    ];

    protected $casts = [
        'approved_items' => 'array',
        'rejected_items' => 'array',
        'approval_order' => 'integer',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
