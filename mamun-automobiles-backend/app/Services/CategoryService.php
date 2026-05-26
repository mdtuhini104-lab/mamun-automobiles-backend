<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use Illuminate\Support\Str;

use App\Services\AuditLogService;

class CategoryService extends BaseService
{
    protected $repository;
    protected $auditLogService;

    public function __construct(CategoryRepository $repository, AuditLogService $auditLogService)
    {
        $this->repository = $repository;
        $this->auditLogService = $auditLogService;
    }

    public function listCategories(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    public function createCategory(array $data)
    {
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $category = $this->repository->create($data);
        $this->auditLogService->log('create', 'Category', $category->id, $data);
        return $category;
    }

    public function getCategory(int $id)
    {
        return $this->repository->findById($id);
    }

    public function updateCategory(int $id, array $data)
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $updated = $this->repository->update($id, $data);
        if ($updated) {
            $this->auditLogService->log('update', 'Category', $id, $data);
        }
        return $updated;
    }

    public function deleteCategory(int $id)
    {
        $deleted = $this->repository->delete($id);
        if ($deleted) {
            $this->auditLogService->log('delete', 'Category', $id);
        }
        return $deleted;
    }
}
