<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'brand',
        'cost_price',
        'sale_price',
        'stock_quantity',
        'low_stock_threshold',
    ];

    /**
     * Get the job card items that use this part.
     */
    public function jobCardItems(): HasMany
    {
        return $this->hasMany(JobCardItem::class);
    }
}
