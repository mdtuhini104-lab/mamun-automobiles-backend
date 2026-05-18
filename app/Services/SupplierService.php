<?php

namespace App\Services;

use App\Repositories\SupplierRepository;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

class SupplierService extends BaseService
{
    protected $supplierRepository;

    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    public function listSuppliers(array $filters = []): Collection
    {
        return $this->supplierRepository->getAll($filters);
    }

    public function createSupplier(array $data): Supplier
    {
        return $this->supplierRepository->create($data);
    }

    public function getSupplier(int $id): ?Supplier
    {
        return $this->supplierRepository->findById($id);
    }

    public function updateSupplier(int $id, array $data): bool
    {
        return $this->supplierRepository->update($id, $data);
    }

    public function deleteSupplier(int $id): bool
    {
        return $this->supplierRepository->delete($id);
    }
}
