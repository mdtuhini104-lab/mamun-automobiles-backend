<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository extends BaseRepository
{
    /**
     * Get all customers with optional filters.
     */
    public function getAll(array $filters = [])
    {
        $query = Customer::query();
        
        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('vehicles', function($vq) use ($search) {
                      $vq->where('registration_number', 'like', "%{$search}%");
                  });
            });
        }
        
        $query->withCount(['vehicles', 'invoices']);
        
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['name', 'email', 'phone', 'created_at'];
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
     * Create a new customer.
     */
    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    /**
     * Find customer by ID.
     */
    public function findById(int $id): ?Customer
    {
        return Customer::with(['vehicles', 'invoices'])->withCount(['vehicles', 'invoices'])->find($id);
    }

    /**
     * Update a customer.
     */
    public function update(int $id, array $data): bool
    {
        $customer = $this->findById($id);
        if (!$customer) {
            return false;
        }
        return $customer->update($data);
    }

    /**
     * Delete a customer.
     */
    public function delete(int $id): bool
    {
        $customer = $this->findById($id);
        if (!$customer) {
            return false;
        }
        return $customer->delete();
    }
}
