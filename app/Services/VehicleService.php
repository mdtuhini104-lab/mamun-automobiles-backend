<?php

namespace App\Services;

use App\Repositories\VehicleRepository;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleService extends BaseService
{
    protected $vehicleRepository;

    public function __construct(VehicleRepository $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function addVehicle(array $data): Vehicle
    {
        return $this->vehicleRepository->create($data);
    }

    public function listVehiclesByCustomer(int $customerId): Collection
    {
        return $this->vehicleRepository->getByCustomerId($customerId);
    }
}
