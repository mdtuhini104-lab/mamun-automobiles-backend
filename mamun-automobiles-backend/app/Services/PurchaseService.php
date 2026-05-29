<?php

namespace App\Services;

use App\Repositories\PurchaseRepository;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\PartRepository;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

use App\Services\AuditLogService;

use App\Services\NotificationService;

class PurchaseService extends BaseService
{
    protected $purchaseRepository;
    protected $purchaseItemRepository;
    protected $partRepository;
    protected $auditLogService;
    protected $notificationService;

    public function __construct(
        PurchaseRepository $purchaseRepository,
        PurchaseItemRepository $purchaseItemRepository,
        PartRepository $partRepository,
        AuditLogService $auditLogService,
        NotificationService $notificationService
    ) {
        $this->purchaseRepository = $purchaseRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
        $this->partRepository = $partRepository;
        $this->auditLogService = $auditLogService;
        $this->notificationService = $notificationService;
    }

    public function listPurchases(array $filters = [])
    {
        return $this->purchaseRepository->getAll($filters);
    }

    public function getPurchase(int $id): ?Purchase
    {
        return $this->purchaseRepository->findById($id);
    }

    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            // Calculate totals
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $paidAmount = $data['paid_amount'] ?? 0;
            $dueAmount = $totalAmount - $paidAmount;
            
            $paymentStatus = 'due';
            if ($paidAmount >= $totalAmount) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            $purchaseData = [
                'supplier_id' => $data['supplier_id'],
                'purchase_no' => $data['purchase_no'] ?? 'PUR-' . time(),
                'purchase_date' => $data['purchase_date'] ?? now()->toDateString(),
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'status' => $data['status'] ?? 'pending',
                'invoice_no' => $data['invoice_no'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            $purchase = $this->purchaseRepository->create($purchaseData);

            foreach ($data['items'] as $item) {
                $this->purchaseItemRepository->create([
                    'purchase_id' => $purchase->id,
                    'part_id' => $item['part_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);

                // If received immediately, update stock
                if ($purchaseData['status'] === 'received') {
                    $this->increaseStock($item['part_id'], $item['quantity'], $purchase->id);
                }
            }

            if ($purchase->status->value === 'received' || $purchase->status->value === 'approved') {
                $this->logPurchaseToSupplierLedger($purchase);
            }

            $this->auditLogService->log('create', 'Purchase', $purchase->id, $data);

            return $purchase;
        });
    }

    public function updatePurchaseStatus(int $id, string $status): bool
    {
        return DB::transaction(function () use ($id, $status) {
            $purchase = $this->purchaseRepository->findById($id);
            if (!$purchase) {
                throw new \Exception('Purchase not found');
            }

            // If it's already approved or received, we don't allow changing status
            if ($purchase->status->value === 'approved' || $purchase->status->value === 'received') {
                throw new \Exception('Cannot change status of an approved or received purchase');
            }

            $updated = $this->purchaseRepository->update($id, ['status' => $status]);

            if ($updated) {
                $this->auditLogService->log('update_status', 'Purchase', $id, ['status' => $status]);

                if ($status === 'approved' || $status === 'received') {
                    $items = $this->purchaseItemRepository->getByPurchaseId($id);
                    foreach ($items as $item) {
                        $this->increaseStock($item->part_id, $item->quantity, $purchase->id);
                    }
                    $this->logPurchaseToSupplierLedger($purchase);
                }

                // Send notification
                if ($status === 'approved' || $status === 'rejected') {
                    $this->notificationService->sendToAllAdmins(new \App\Notifications\PurchaseApprovalNotification($purchase, $status));
                }
            }

            return $updated;
        });
    }

    public function deletePurchase(int $id): bool
    {
        $deleted = $this->purchaseRepository->delete($id);
        if ($deleted) {
            $this->auditLogService->log('delete', 'Purchase', $id);
        }
        return $deleted;
    }

    public function getLowStockParts(): Collection
    {
        return $this->partRepository->getLowStock();
    }

    protected function increaseStock(int $partId, float $quantity, ?int $purchaseId = null): void
    {
        $partService = app(\App\Services\PartService::class);
        $partService->adjustStock(
            $partId,
            $quantity,
            'in',
            'Purchase stock received',
            'purchase',
            $purchaseId
        );
    }

    /**
     * Log the purchase and paid amounts into the supplier ledger.
     */
    protected function logPurchaseToSupplierLedger(Purchase $purchase): void
    {
        // Prevent duplicate ledger logging for the same purchase
        $alreadyLogged = \App\Models\SupplierLedger::where('supplier_id', $purchase->supplier_id)
            ->where('notes', 'like', "%{$purchase->purchase_no}%")
            ->exists();

        if (!$alreadyLogged) {
            // Log the purchase charge (debit - increases outstanding dues)
            \App\Models\SupplierLedger::logTransaction(
                $purchase->supplier_id,
                $purchase->total_amount,
                'purchase',
                "Purchase order {$purchase->purchase_no} finalized"
            );

            // Log any payment made on this purchase (credit - decreases outstanding dues)
            if ($purchase->paid_amount > 0) {
                \App\Models\SupplierLedger::logTransaction(
                    $purchase->supplier_id,
                    $purchase->paid_amount,
                    'payment',
                    "Payment for purchase order {$purchase->purchase_no}"
                );
            }
        }
    }
}
