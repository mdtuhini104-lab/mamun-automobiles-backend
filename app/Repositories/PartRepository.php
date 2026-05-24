<?php

namespace App\Repositories;

use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;

class PartRepository extends BaseRepository
{
    /**
     * Create a new part.
     */
    public function create(array $data): Part
    {
        return Part::create($data);
    }

    /**
     * Get all parts with optional filters.
     */
    public function getAll(array $filters = [])
    {
        $query = Part::query();
        
        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }
        
        // Brand filter
        if (isset($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }
        
        // Stock availability filter
        if (isset($filters['stock_availability'])) {
            if ($filters['stock_availability'] === 'available') {
                $query->where('stock_quantity', '>', 0);
            } elseif ($filters['stock_availability'] === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            }
        }
        
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        // Validate sort_by to prevent SQL injection
        $allowedSorts = ['name', 'sku', 'brand', 'cost_price', 'sale_price', 'stock_quantity', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Pagination
        $perPage = $filters['per_page'] ?? 15;
        
        return $query->paginate($perPage);
    }

    /**
     * Find part by ID.
     */
    public function findById(int $id): ?Part
    {
        return Part::find($id);
    }

    /**
     * Find part by SKU.
     */
    public function findBySku(string $sku): ?Part
    {
        return Part::where('sku', $sku)->first();
    }

    /**
     * Get low stock parts.
     */
    public function getLowStock(): Collection
    {
        return Part::whereRaw('stock_quantity <= low_stock_threshold')->get();
    }

    /**
     * Update a part.
     */
    public function update(int $id, array $data): bool
    {
        $part = Part::find($id);
        if (!$part) {
            return false;
        }
        return $part->update($data);
    }

    /**
     * Delete a part.
     */
    public function delete(int $id): bool
    {
        $part = Part::find($id);
        if (!$part) {
            return false;
        }
        return $part->delete();
    }
}
