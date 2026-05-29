<?php

namespace App\Models;

use App\Traits\MultitenantSafe;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, SoftDeletes, MultitenantSafe, BranchScoped;

    protected static function booted()
    {
        static::saved(function ($customer) {
            \Illuminate\Support\Facades\Cache::forget("quick_load_customer_id_" . $customer->id);
            \Illuminate\Support\Facades\Cache::forget("quick_load_customer_phone_" . md5($customer->phone));
            if ($customer->email) {
                \Illuminate\Support\Facades\Cache::forget("quick_load_customer_email_" . md5($customer->email));
            }
        });
        static::deleted(function ($customer) {
            \Illuminate\Support\Facades\Cache::forget("quick_load_customer_id_" . $customer->id);
            \Illuminate\Support\Facades\Cache::forget("quick_load_customer_phone_" . md5($customer->phone));
            if ($customer->email) {
                \Illuminate\Support\Facades\Cache::forget("quick_load_customer_email_" . md5($customer->email));
            }
        });
    }
    
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

    public function getPricingTierAttribute()
    {
        return strtolower($this->tag ?? 'regular');
    }
}

