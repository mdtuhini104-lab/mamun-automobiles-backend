<?php

namespace App\Services;

use App\Repositories\StockAdjustmentRepository;
use App\Repositories\PartRepository;
use Illuminate\Support\Facades\DB;

use App\Services\AuditLogService;

class StockAdjustmentService extends BaseService
{
    protected $repository;
    protected $partRepository;
    protected $auditLogService;

    public function __construct(StockAdjustmentRepository $repository, PartRepository $partRepository, AuditLogService $auditLogService)
    {
        $this->repository = $repository;
        $this->partRepository = $partRepository;
        $this->auditLogService = $auditLogService;
    }

    public function listAdjustments(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    public function createAdjustment(array $data)
    {
        return DB::transaction(function () use ($data) {
            $part = $this->partRepository->findById($data['part_id']);
            if (!$part) {
                throw new \Exception('Part not found');
            }

            if ($data['type'] === 'out' && $part->stock_quantity < $data['quantity']) {
                throw new \Exception('Insufficient stock for adjustment');
            }

            $adjustment = $this->repository->create($data);

            $newQuantity = $part->stock_quantity;
            if ($data['type'] === 'in') {
                $newQuantity += $data['quantity'];
            } elseif ($data['type'] === 'out') {
                $newQuantity -= $data['quantity'];
            }

            $this->partRepository->update($data['part_id'], ['stock_quantity' => $newQuantity]);

            $this->auditLogService->log('create', 'StockAdjustment', $adjustment->id, $data);

            return $adjustment;
        });
    }

    public function getAdjustment(int $id)
    {
        return $this->repository->findById($id);
    }

    public function updateAdjustment(int $id, array $data): bool
    {
        // Only allow updating notes/reason to avoid stock inconsistency
        $updatableData = array_intersect_key($data, array_flip(['notes', 'reason']));
        
        $updated = $this->repository->update($id, $updatableData);
        if ($updated) {
            $this->auditLogService->log('update', 'StockAdjustment', $id, $updatableData);
        }
        return $updated;
    }

    public function deleteAdjustment(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $adjustment = $this->repository->findById($id);
            if (!$adjustment) {
                return false;
            }

            $part = $this->partRepository->findById($adjustment->part_id);
            if (!$part) {
                throw new \Exception('Part not found');
            }

            if ($adjustment->type === 'in' && $part->stock_quantity < $adjustment->quantity) {
                throw new \Exception('Cannot delete adjustment: insufficient stock');
            }

            // Reverse stock
            $newQuantity = $part->stock_quantity;
            if ($adjustment->type === 'in') {
                $newQuantity -= $adjustment->quantity;
            } elseif ($adjustment->type === 'out') {
                $newQuantity += $adjustment->quantity;
            }

            $this->partRepository->update($adjustment->part_id, ['stock_quantity' => $newQuantity]);

            $deleted = $this->repository->delete($id);
            if ($deleted) {
                $this->auditLogService->log('delete', 'StockAdjustment', $id);
            }
            return $deleted;
        });
    }
}
