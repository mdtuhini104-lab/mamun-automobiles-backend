<?php

namespace App\Repositories;

use App\Models\WorkshopBay;

class WorkshopBayRepository extends BaseRepository
{
    public function __construct(WorkshopBay $model)
    {
        parent::__construct($model);
    }

    public function create(array $data): WorkshopBay
    {
        return WorkshopBay::create($data);
    }

    public function getAll(array $filters = [])
    {
        $query = WorkshopBay::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return $query->get();
    }

    public function findById(int $id): ?WorkshopBay
    {
        return WorkshopBay::find($id);
    }
}
