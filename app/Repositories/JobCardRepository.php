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
    public function getAll(array $filters = []): Collection
    {
        $query = JobCard::with(['customer', 'vehicle', 'mechanic']);
        
        if (isset($filters['status'])) {
            $query->where('service_status', $filters['status']);
        }
        
        return $query->get();
    }

    /**
     * Find job card by ID.
     */
    public function findById(int $id): ?JobCard
    {
        return JobCard::with(['customer', 'vehicle', 'mechanic', 'items.part'])->find($id);
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
