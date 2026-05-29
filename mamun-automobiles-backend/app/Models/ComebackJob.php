<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComebackJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_job_card_id',
        'comeback_job_card_id',
        'reason',
        'technician_at_fault_id',
    ];

    public function originalJobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class, 'original_job_card_id');
    }

    public function comebackJobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class, 'comeback_job_card_id');
    }

    public function technicianAtFault(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_at_fault_id');
    }
}
