<?php

namespace App\Services;

use App\Repositories\PartRepository;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;

class PartService extends BaseService
{
    protected $partRepository;

    public function __construct(PartRepository $partRepository)
    {
        $this->partRepository = $partRepository;
    }

    public function createPart(array $data): Part
    {
        return $this->partRepository->create($data);
    }

    public function listParts(array $filters = []): Collection
    {
        return $this->partRepository->getAll($filters);
    }

    public function getPart(int $id): ?Part
    {
        return $this->partRepository->findById($id);
    }

    public function getLowStockParts(): Collection
    {
        return $this->partRepository->getLowStock();
    }

    public function updatePart(int $id, array $data): bool
    {
        return $this->partRepository->update($id, $data);
    }

    public function deletePart(int $id): bool
    {
        return $this->partRepository->delete($id);
    }
}
