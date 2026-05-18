<?php

namespace App\Services;

use App\Repositories\PurchaseRepository;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\PartRepository;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class PurchaseService extends BaseService
{
    protected $purchaseRepository;
    protected $purchaseItemRepository;
    protected $partRepository;

    public function __construct(
        PurchaseRepository $purchaseRepository,
        PurchaseItemRepository $purchaseItemRepository,
        PartRepository $partRepository
    ) {
        $this->purchaseRepository = $purchaseRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
        $this->partRepository = $partRepository;
    }

    public function listPurchases(array $filters = []): Collection
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
                    $this->increaseStock($item['part_id'], $item['quantity']);
                }
            }

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

            // If it's already received, we don't allow changing status back or to cancelled easily
            // unless we handle stock reduction, which can be complex. Let's prevent it for now.
            if ($purchase->status->value === 'received') {
                throw new \Exception('Cannot change status of a received purchase');
            }

            $updated = $this->purchaseRepository->update($id, ['status' => $status]);

            if ($updated && $status === 'received') {
                $items = $this->purchaseItemRepository->getByPurchaseId($id);
                foreach ($items as $item) {
                    $this->increaseStock($item->part_id, $item->quantity);
                }
            }

            return $updated;
        });
    }

    public function getLowStockParts(): Collection
    {
        return $this->partRepository->getLowStock();
    }

    protected function increaseStock(int $partId, float $quantity): void
    {
        $part = $this->partRepository->findById($partId);
        if ($part) {
            $part->stock_quantity += $quantity;
            $part->save();
        }
    }
}
