<?php

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

class AccountRepository extends BaseRepository
{
    public function __construct(Account $model)
    {
        parent::__construct($model);
    }

    public function getAll(array $filters = [])
    {
        $query = Account::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('account_no', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['name', 'type', 'balance', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function create(array $data): Account
    {
        return Account::create($data);
    }

    public function findById(int $id): ?Account
    {
        return Account::find($id);
    }

    public function update(int $id, array $data): bool
    {
        $account = $this->findById($id);
        if (!$account) {
            return false;
        }
        return $account->update($data);
    }

    public function delete(int $id): bool
    {
        $account = $this->findById($id);
        if (!$account) {
            return false;
        }
        return $account->delete();
    }

    public function updateBalance(int $id, float $amount, string $operation = 'add'): bool
    {
        $account = $this->findById($id);
        if (!$account) {
            return false;
        }

        if ($operation === 'add') {
            $account->balance += $amount;
        } elseif ($operation === 'subtract') {
            $account->balance -= $amount;
        }

        return $account->save();
    }
}
