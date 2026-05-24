<?php

namespace App\Services;

use App\Repositories\VehicleRepository;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

use App\Services\AuditLogService;

class VehicleService extends BaseService
{
    protected VehicleRepository $vehicleRepository;
    protected AuditLogService $auditLogService;

    public function __construct(VehicleRepository $vehicleRepository, AuditLogService $auditLogService)
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->auditLogService = $auditLogService;
    }

    public function listVehicles(array $filters = [])
    {
        return $this->vehicleRepository->getAll($filters);
    }

    public function addVehicle(array $data): Vehicle
    {
        $vehicle = $this->vehicleRepository->create($data);
        $this->auditLogService->log('create', 'Vehicle', $vehicle->id, $data);
        return $vehicle;
    }

    public function listVehiclesByCustomer(int $customerId): Collection
    {
        return $this->vehicleRepository->getByCustomerId($customerId);
    }

    public function updateVehicle(int $id, array $data): bool
    {
        $updated = $this->vehicleRepository->update($id, $data);
        if ($updated) {
            $this->auditLogService->log('update', 'Vehicle', $id, $data);
        }
        return $updated;
    }

    public function deleteVehicle(int $id): bool
    {
        $deleted = $this->vehicleRepository->delete($id);
        if ($deleted) {
            $this->auditLogService->log('delete', 'Vehicle', $id);
        }
        return $deleted;
    }

    public function getVehicle(int $id): ?Vehicle
    {
        return $this->vehicleRepository->findById($id);
    }
}
