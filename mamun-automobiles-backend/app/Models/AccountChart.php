<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\MultitenantSafe;

class AccountChart extends Model
{
    use MultitenantSafe;

    protected $table = 'accounts_chart';

    protected $fillable = [
        'tenant_id',
        'account_code',
        'account_name',
        'account_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function journalItems(): HasMany
    {
        return $this->hasMany(JournalItem::class, 'account_id');
    }
}
