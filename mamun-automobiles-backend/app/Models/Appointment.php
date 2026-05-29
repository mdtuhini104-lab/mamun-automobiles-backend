<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultitenantSafe;
use App\Traits\BranchScoped;

class Appointment extends Model
{
    use MultitenantSafe, BranchScoped;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'customer_id',
        'vehicle_id',
        'mechanic_id',
        'appointment_date',
        'appointment_time',
        'service_type',
        'status',
        'remarks'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mechanic()
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }
}
