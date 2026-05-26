<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, SoftDeletes;
    
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'balance',
        'password',
        'tag',
        'loyalty_points',
        'notes'
    ];

    protected $hidden = [
        'password',
    ];

    public function vehicles(): HasMany { return $this->hasMany(Vehicle::class); }
    public function jobCards(): HasMany { return $this->hasMany(JobCard::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
    public function activities(): HasMany { return $this->hasMany(CustomerActivity::class)->orderBy('created_at', 'desc'); }
}

