<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashbookTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashbook_id', 'reference_type', 'reference_id', 'transaction_type',
        'category', 'amount', 'balance_after', 'description', 'payment_method',
        'created_by', 'approved_by'
    ];

    public function cashbook()
    {
        return $this->belongsTo(Cashbook::class);
    }
}
