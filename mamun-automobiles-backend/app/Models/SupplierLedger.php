<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'amount',
        'transaction_type',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public static function logTransaction(int $supplierId, float $amount, string $transactionType, ?string $notes = null): self
    {
        return self::create([
            'supplier_id' => $supplierId,
            'amount' => $amount,
            'transaction_type' => $transactionType,
            'notes' => $notes,
        ]);
    }
}
