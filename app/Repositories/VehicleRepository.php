<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleRepository extends BaseRepository
{
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
}
