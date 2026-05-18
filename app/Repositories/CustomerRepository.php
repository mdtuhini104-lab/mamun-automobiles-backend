<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository extends BaseRepository
{
    /**
     * Get all customers.
     */
    public function getAll(): Collection
    {
        return Customer::all();
    }

    /**
     * Create a new customer.
     */
    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    /**
     * Find customer by ID.
     */
    public function findById(int $id): ?Customer
    {
        return Customer::find($id);
    }
}
