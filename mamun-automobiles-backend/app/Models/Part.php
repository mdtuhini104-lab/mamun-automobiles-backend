<?php

namespace App\Models;

use App\Traits\MultitenantSafe;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Part extends Model
{
    use SoftDeletes, MultitenantSafe, BranchScoped;
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'brand',
        'cost_price',
        'sale_price',
        'stock_quantity',
        'reserved_quantity',
        'low_stock_threshold',
        'category_id',
        'rack_location',
        'unit_type',
        'image_path',
    ];

    /**
     * Get the category that owns the part.
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the job card items that use this part.
     */
    public function jobCardItems(): HasMany
    {
        return $this->hasMany(JobCardItem::class);
    }
}
