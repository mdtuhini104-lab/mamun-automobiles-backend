<?php

namespace App\Services;

use App\Repositories\SupplierRepository;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

use App\Services\AuditLogService;

class SupplierService extends BaseService
{
    protected SupplierRepository $supplierRepository;
    protected AuditLogService $auditLogService;

    public function __construct(SupplierRepository $supplierRepository, AuditLogService $auditLogService)
    {
        $this->supplierRepository = $supplierRepository;
        $this->auditLogService = $auditLogService;
    }

    public function listSuppliers(array $filters = [])
    {
        return $this->supplierRepository->getAll($filters);
    }

    public function createSupplier(array $data): Supplier
    {
        $supplier = $this->supplierRepository->create($data);
        $this->auditLogService->log('create', 'Supplier', $supplier->id, $data);
        return $supplier;
    }

    public function getSupplier(int $id): ?Supplier
    {
        return $this->supplierRepository->findById($id);
    }

    public function updateSupplier(int $id, array $data): bool
    {
        $updated = $this->supplierRepository->update($id, $data);
        if ($updated) {
            $this->auditLogService->log('update', 'Supplier', $id, $data);
        }
        return $updated;
    }

    public function deleteSupplier(int $id): bool
    {
        $deleted = $this->supplierRepository->delete($id);
        if ($deleted) {
            $this->auditLogService->log('delete', 'Supplier', $id);
        }
        return $deleted;
    }

    /**
     * Record a payment made to a supplier.
     */
    public function recordPayment(int $supplierId, float $amount, ?string $notes = null): \App\Models\SupplierLedger
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($supplierId, $amount, $notes) {
            $supplier = $this->getSupplier($supplierId);
            if (!$supplier) {
                throw new \Exception('Supplier not found');
            }

            $ledger = \App\Models\SupplierLedger::logTransaction($supplierId, $amount, 'payment', $notes);
            
            $this->auditLogService->log('record_payment', 'Supplier', $supplierId, [
                'amount' => $amount,
                'notes' => $notes,
                'ledger_id' => $ledger->id
            ]);

            return $ledger;
        });
    }

    /**
     * Get the ledger history for a supplier.
     */
    public function getLedgerHistory(int $supplierId)
    {
        $supplier = $this->getSupplier($supplierId);
        if (!$supplier) {
            throw new \Exception('Supplier not found');
        }

        return $supplier->ledgers()->latest()->get();
    }
}
