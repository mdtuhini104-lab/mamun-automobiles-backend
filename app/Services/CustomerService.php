<?php

namespace App\Services;

use App\Repositories\CustomerRepository;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

use App\Services\AuditLogService;

class CustomerService extends BaseService
{
    protected CustomerRepository $customerRepository;
    protected AuditLogService $auditLogService;

    public function __construct(CustomerRepository $customerRepository, AuditLogService $auditLogService)
    {
        $this->customerRepository = $customerRepository;
        $this->auditLogService = $auditLogService;
    }

    /**
     * List customers with filters.
     */
    public function listCustomers(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->customerRepository->getAll($filters);
    }

    public function createCustomer(array $data): Customer
    {
        $customer = $this->customerRepository->create($data);
        $this->auditLogService->log('create', 'Customer', $customer->id, $data);
        return $customer;
    }

    public function getCustomer(int $id): ?Customer
    {
        return $this->customerRepository->findById($id);
    }

    public function updateCustomer(int $id, array $data): bool
    {
        $updated = $this->customerRepository->update($id, $data);
        if ($updated) {
            $this->auditLogService->log('update', 'Customer', $id, $data);
        }
        return $updated;
    }

    public function deleteCustomer(int $id): bool
    {
        $deleted = $this->customerRepository->delete($id);
        if ($deleted) {
            $this->auditLogService->log('delete', 'Customer', $id);
        }
        return $deleted;
    }
}
