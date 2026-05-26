<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Repositories\AccountRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\PurchaseRepository;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

use App\Services\AuditLogService;

class TransactionService extends BaseService
{
    protected $repository;
    protected $accountRepository;
    protected $invoiceRepository;
    protected $purchaseRepository;
    protected $auditLogService;

    public function __construct(
        TransactionRepository $repository, 
        AccountRepository $accountRepository,
        InvoiceRepository $invoiceRepository,
        PurchaseRepository $purchaseRepository,
        AuditLogService $auditLogService
    ) {
        $this->repository = $repository;
        $this->accountRepository = $accountRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->auditLogService = $auditLogService;
    }

    public function listTransactions(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    public function createTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            $transaction = $this->repository->create($data);

            // Update account balance
            $operation = 'add';
            if ($data['type'] === 'expense') {
                $operation = 'subtract';
            }

            $this->accountRepository->updateBalance($data['account_id'], $data['amount'], $operation);

            // Handle references
            if (isset($data['reference_type']) && isset($data['reference_id'])) {
                if ($data['reference_type'] === 'invoice') {
                    $this->handleInvoicePayment($data['reference_id'], $data['amount']);
                } elseif ($data['reference_type'] === 'purchase') {
                    $this->handlePurchasePayment($data['reference_id'], $data['amount']);
                }
            }

            $this->auditLogService->log('create', 'Transaction', $transaction->id, $data);

            return $transaction;
        });
    }

    public function getTransaction(int $id)
    {
        return $this->repository->findById($id);
    }

    public function updateTransaction(int $id, array $data): bool
    {
        // Only allow updating description and date to avoid balance inconsistency
        $updatableData = array_intersect_key($data, array_flip(['description', 'date']));
        
        $updated = $this->repository->update($id, $updatableData);
        if ($updated) {
            $this->auditLogService->log('update', 'Transaction', $id, $updatableData);
        }
        return $updated;
    }

    public function deleteTransaction(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $transaction = $this->repository->findById($id);
            if (!$transaction) {
                return false;
            }

            // Reverse account balance
            $operation = 'subtract';
            if ($transaction->type === 'expense') {
                $operation = 'add';
            }

            $this->accountRepository->updateBalance($transaction->account_id, $transaction->amount, $operation);

            $deleted = $this->repository->delete($id);
            if ($deleted) {
                $this->auditLogService->log('delete', 'Transaction', $id);
            }
            return $deleted;
        });
    }

    protected function handleInvoicePayment(int $invoiceId, float $amount)
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);
        if ($invoice) {
            $paid = $invoice->paid_amount + $amount;
            $due = $invoice->grand_total - $paid;
            $status = 'partial';
            if ($due <= 0) {
                $due = 0;
                $status = 'paid';
            }
            $this->invoiceRepository->update($invoiceId, [
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
            ]);
        }
    }

    protected function handlePurchasePayment(int $purchaseId, float $amount)
    {
        $purchase = $this->purchaseRepository->findById($purchaseId);
        if ($purchase) {
            $paid = $purchase->paid_amount + $amount;
            $due = $purchase->total_amount - $paid;
            $status = PaymentStatus::PARTIAL;
            if ($due <= 0) {
                $due = 0;
                $status = PaymentStatus::PAID;
            }
            $this->purchaseRepository->update($purchaseId, [
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
            ]);
        }
    }
}
