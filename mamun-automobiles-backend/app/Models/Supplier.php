<?php

namespace App\Models;

use App\Enums\SupplierStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company_name',
        'status',
        'tax_number',
    ];

    protected $casts = [
        'status' => SupplierStatus::class,
    ];

    /**
     * Get the purchases for the supplier.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the ledgers for the supplier.
     */
    public function ledgers(): HasMany
    {
        return $this->hasMany(SupplierLedger::class);
    }

    /**
     * Get dynamic outstanding due balance for the supplier.
     */
    public function getBalanceAttribute()
    {
        $purchaseSum = $this->ledgers()->where('transaction_type', 'purchase')->sum('amount');
        $paymentSum = $this->ledgers()->where('transaction_type', 'payment')->sum('amount');
        return round($purchaseSum - $paymentSum, 2);
    }
}
