<?php

namespace App\Repositories;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Collection;

class PurchaseRepository extends BaseRepository
{
    public function getAll(array $filters = [])
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
        
        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('purchase_no', 'like', "%{$search}%")
                  ->orWhere('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['purchase_no', 'purchase_date', 'total_amount', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
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

    public function getTotals(array $filters = []): array
    {
        $query = \App\Models\Purchase::query();

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('purchase_date', [$filters['start_date'], $filters['end_date']]);
        }

        return [
            'total_amount' => (float) $query->sum('total_amount'),
            'paid_amount' => (float) $query->sum('paid_amount'),
            'due_amount' => (float) $query->sum('due_amount'),
        ];
    }
}
