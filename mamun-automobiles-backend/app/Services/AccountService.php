<?php

namespace App\Services;

use App\Repositories\AccountRepository;

use App\Services\AuditLogService;

class AccountService extends BaseService
{
    protected AccountRepository $repository;
    protected AuditLogService $auditLogService;

    public function __construct(AccountRepository $repository, AuditLogService $auditLogService)
    {
        $this->repository = $repository;
        $this->auditLogService = $auditLogService;
    }

    public function listAccounts(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    public function createAccount(array $data)
    {
        $account = $this->repository->create($data);
        $this->auditLogService->log('create', 'Account', $account->id, $data);
        return $account;
    }

    public function getAccount(int $id)
    {
        return $this->repository->findById($id);
    }

    public function updateAccount(int $id, array $data)
    {
        $updated = $this->repository->update($id, $data);
        if ($updated) {
            $this->auditLogService->log('update', 'Account', $id, $data);
        }
        return $updated;
    }

    public function deleteAccount(int $id)
    {
        $deleted = $this->repository->delete($id);
        if ($deleted) {
            $this->auditLogService->log('delete', 'Account', $id);
        }
        return $deleted;
    }
}
