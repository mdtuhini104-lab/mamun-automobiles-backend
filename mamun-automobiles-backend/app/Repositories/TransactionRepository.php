<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    public function getAll(array $filters = [])
    {
        $query = Transaction::query()->with('account');

        if (isset($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['type', 'amount', 'date', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function findById(int $id): ?Transaction
    {
        return Transaction::with('account')->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return false;
        }
        return $transaction->update($data);
    }

    public function delete(int $id): bool
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return false;
        }
        return $transaction->delete();
    }

    public function getTotals(array $filters = []): array
    {
        $query = \App\Models\Transaction::query();

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        return [
            'income' => (float) $query->clone()->where('type', 'income')->sum('amount'),
            'expense' => (float) $query->clone()->where('type', 'expense')->sum('amount'),
        ];
    }
}
