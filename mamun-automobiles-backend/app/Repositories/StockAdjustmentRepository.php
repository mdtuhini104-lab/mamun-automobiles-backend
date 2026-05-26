<?php

namespace App\Repositories;

use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Collection;

class StockAdjustmentRepository extends BaseRepository
{
    public function __construct(StockAdjustment $model)
    {
        parent::__construct($model);
    }

    public function getAll(array $filters = [])
    {
        $query = StockAdjustment::query()->with(['part', 'user']);

        if (isset($filters['part_id'])) {
            $query->where('part_id', $filters['part_id']);
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
        
        $allowedSorts = ['type', 'quantity', 'date', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function create(array $data): StockAdjustment
    {
        return StockAdjustment::create($data);
    }

    public function findById(int $id): ?StockAdjustment
    {
        return StockAdjustment::with(['part', 'user'])->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $adjustment = StockAdjustment::find($id);
        if (!$adjustment) {
            return false;
        }
        return $adjustment->update($data);
    }

    public function delete(int $id): bool
    {
        $adjustment = StockAdjustment::find($id);
        if (!$adjustment) {
            return false;
        }
        return $adjustment->delete();
    }
}
