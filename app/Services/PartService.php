<?php

namespace App\Services;

use App\Repositories\PartRepository;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;

use App\Services\AuditLogService;

class PartService extends BaseService
{
    protected $partRepository;
    protected $auditLogService;

    public function __construct(PartRepository $partRepository, AuditLogService $auditLogService)
    {
        $this->partRepository = $partRepository;
        $this->auditLogService = $auditLogService;
    }

    public function createPart(array $data): Part
    {
        $part = $this->partRepository->create($data);
        $this->auditLogService->log('create', 'Part', $part->id, $data);
        return $part;
    }

    public function listParts(array $filters = [])
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
        $updated = $this->partRepository->update($id, $data);
        if ($updated) {
            $this->auditLogService->log('update', 'Part', $id, $data);
        }
        return $updated;
    }

    public function adjustStock(int $partId, int $quantity, string $type, string $notes = null, string $referenceType = null, int $referenceId = null): bool
    {
        $part = $this->getPart($partId);
        if (!$part) return false;

        $newStock = $type === 'in' ? $part->stock_quantity + $quantity : $part->stock_quantity - $quantity;
        
        // Ensure stock doesn't go below 0 on 'out' type (unless allowed, but we'll allow negative for simplicity or just enforce 0)
        // Let's allow it but log it
        
        $this->updatePart($partId, ['stock_quantity' => $newStock]);

        \App\Models\InventoryTransaction::create([
            'part_id' => $partId,
            'type' => $type,
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'unit_cost' => $part->cost_price,
            'notes' => $notes,
        ]);

        return true;
    }

    public function deletePart(int $id): bool
    {
        $deleted = $this->partRepository->delete($id);
        if ($deleted) {
            $this->auditLogService->log('delete', 'Part', $id);
        }
        return $deleted;
    }
}
