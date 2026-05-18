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
    public function getAll(array $filters = []): Collection
    {
        $query = Part::query();
        
        if (isset($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }
        
        if (isset($filters['stock_availability'])) {
            if ($filters['stock_availability'] === 'available') {
                $query->where('stock_quantity', '>', 0);
            } elseif ($filters['stock_availability'] === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            }
        }
        
        return $query->get();
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
