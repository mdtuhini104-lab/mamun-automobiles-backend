<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = ['user_id', 'month', 'year', 'basic_salary', 'overtime_amount', 'bonus', 'deductions', 'net_salary', 'status', 'payment_date'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}

