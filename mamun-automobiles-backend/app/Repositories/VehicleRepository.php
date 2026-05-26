<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleRepository extends BaseRepository
{
    /**
     * Get all vehicles with optional filters.
     */
    public function getAll(array $filters = [])
    {
        $query = Vehicle::with('customer');
        
        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('license_plate', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%");
            });
        }
        
        // Customer filter
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['license_plate', 'make', 'model', 'year', 'created_at'];
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
     * Create a new vehicle.
     */
    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    /**
     * Get vehicles by customer ID.
     */
    public function getByCustomerId(int $customerId): Collection
    {
        return Vehicle::where('customer_id', $customerId)->get();
    }

    /**
     * Find vehicle by ID.
     */
    public function findById(int $id): ?Vehicle
    {
        return Vehicle::with(['customer', 'jobCards'])->find($id);
    }

    /**
     * Update a vehicle.
     */
    public function update(int $id, array $data): bool
    {
        $vehicle = $this->findById($id);
        if (!$vehicle) {
            return false;
        }
        return $vehicle->update($data);
    }

    /**
     * Delete a vehicle.
     */
    public function delete(int $id): bool
    {
        $vehicle = $this->findById($id);
        if (!$vehicle) {
            return false;
        }
        return $vehicle->delete();
    }
}
