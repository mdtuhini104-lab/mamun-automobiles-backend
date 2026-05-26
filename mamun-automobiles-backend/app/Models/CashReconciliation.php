<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashbook_id', 'expected_balance', 'actual_balance', 'difference',
        'status', 'notes', 'verified_by'
    ];

    public function cashbook()
    {
        return $this->belongsTo(Cashbook::class);
    }
}
