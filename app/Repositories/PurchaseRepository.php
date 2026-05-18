<?php

namespace App\Repositories;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Collection;

class PurchaseRepository extends BaseRepository
{
    public function getAll(array $filters = []): Collection
    {
        $query = Purchase::with(['supplier', 'items.part']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['purchase_no'])) {
            $query->where('purchase_no', $filters['purchase_no']);
        }

        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $query->whereBetween('purchase_date', [$filters['from_date'], $filters['to_date']]);
        }

        return $query->get();
    }

    public function create(array $data): Purchase
    {
        return Purchase::create($data);
    }

    public function findById(int $id): ?Purchase
    {
        return Purchase::with(['supplier', 'items.part'])->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return false;
        }
        return $purchase->update($data);
    }

    public function delete(int $id): bool
    {
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return false;
        }
        return $purchase->delete();
    }
}
