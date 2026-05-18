<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository extends BaseRepository
{
    public function getAll(array $filters = []): Collection
    {
        $query = Supplier::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function findById(int $id): ?Supplier
    {
        return Supplier::find($id);
    }

    public function update(int $id, array $data): bool
    {
        $supplier = $this->findById($id);
        if (!$supplier) {
            return false;
        }
        return $supplier->update($data);
    }

    public function delete(int $id): bool
    {
        $supplier = $this->findById($id);
        if (!$supplier) {
            return false;
        }
        return $supplier->delete();
    }
}
