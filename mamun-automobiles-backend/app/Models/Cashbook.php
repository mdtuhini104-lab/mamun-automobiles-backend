<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'name', 'type', 'opening_balance', 'current_balance', 'status', 'created_by'
    ];

    public function transactions()
    {
        return $this->hasMany(CashbookTransaction::class);
    }

    public function dailyClosings()
    {
        return $this->hasMany(DailyClosing::class);
    }
}
