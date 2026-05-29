<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationVersionSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'version',
        'snapshot_data',
        'created_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'snapshot_data' => 'array',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
