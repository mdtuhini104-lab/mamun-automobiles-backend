<?php

namespace App\Repositories;

use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Collection;

class PurchaseItemRepository extends BaseRepository
{
    public function create(array $data): PurchaseItem
    {
        return PurchaseItem::create($data);
    }

    public function getByPurchaseId(int $purchaseId): Collection
    {
        return PurchaseItem::where('purchase_id', $purchaseId)->with('part')->get();
    }
}
