<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'cashbook_id', 'opening_balance', 'total_income', 'total_expense',
        'total_due_collected', 'total_invoice_sales', 'manual_adjustment', 'closing_balance',
        'system_balance', 'difference_amount', 'closing_notes', 'closed_by', 'closed_at'
    ];

    public function cashbook()
    {
        return $this->belongsTo(Cashbook::class);
    }
}
