<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\MultitenantSafe;
use App\Traits\BranchScoped;

class JournalEntry extends Model
{
    use MultitenantSafe, BranchScoped;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'entry_date',
        'reference_no',
        'description',
        'created_by'
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(JournalItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
