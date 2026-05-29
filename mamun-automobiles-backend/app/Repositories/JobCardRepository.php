<?php

namespace App\Repositories;

use App\Models\JobCard;
use Illuminate\Database\Eloquent\Collection;

class JobCardRepository extends BaseRepository
{
    /**
     * Create a new job card.
     */
    public function create(array $data): JobCard
    {
        return JobCard::create($data);
    }

    /**
     * Get all job cards with optional filters.
     */
    public function getAll(array $filters = [])
    {
        $query = JobCard::with(['customer', 'vehicle', 'mechanic']);
        
        if (isset($filters['status'])) {
            $query->where('service_status', $filters['status']);
        }
        
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        if (isset($filters['vehicle_id'])) {
            $query->where('vehicle_id', $filters['vehicle_id']);
        }
        
        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('complaint', 'like', "%{$search}%")
                  ->orWhere('diagnosis', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vehicle', function ($vq) use ($search) {
                      $vq->where('license_plate', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['service_date', 'estimated_cost', 'created_at'];
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
     * Find job card by ID.
     */
    public function findById(int $id): ?JobCard
    {
        return JobCard::with([
            'customer',
            'vehicle',
            'mechanic',
            'items.part',
            'department',
            'workshopBay',
            'assignments.employee.user',
            'tasks.assignments.employee.user',
            'workflowHistory.user'
        ])->find($id);
    }

    /**
     * Get job cards by vehicle ID (Service History).
     */
    public function getByVehicleId(int $vehicleId): Collection
    {
        return JobCard::where('vehicle_id', $vehicleId)->with(['customer', 'mechanic'])->get();
    }

    /**
     * Get job cards by customer ID (Customer History).
     */
    public function getByCustomerId(int $customerId): Collection
    {
        return JobCard::where('customer_id', $customerId)->with(['vehicle', 'mechanic'])->get();
    }

    /**
     * Update a job card.
     */
    public function update(int $id, array $data): bool
    {
        $jobCard = JobCard::find($id);
        if (!$jobCard) {
            return false;
        }
        return $jobCard->update($data);
    }

    /**
     * Delete a job card.
     */
    public function delete(int $id): bool
    {
        $jobCard = JobCard::find($id);
        if (!$jobCard) {
            return false;
        }
        return $jobCard->delete();
    }
}
