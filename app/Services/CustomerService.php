<?php

namespace App\Services;

use App\Repositories\CustomerRepository;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerService extends BaseService
{
    protected $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function listCustomers(): Collection
    {
        return $this->customerRepository->getAll();
    }

    public function createCustomer(array $data): Customer
    {
        return $this->customerRepository->create($data);
    }

    public function getCustomer(int $id): ?Customer
    {
        return $this->customerRepository->findById($id);
    }
}
