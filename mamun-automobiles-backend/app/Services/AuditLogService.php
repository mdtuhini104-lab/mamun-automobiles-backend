<?php

namespace App\Services;

use App\Repositories\AuditLogRepository;
use App\Models\AuditLog;

class AuditLogService extends BaseService
{
    protected $repository;

    public function __construct(AuditLogRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List audit logs with filters.
     */
    public function listLogs(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Log an action.
     */
    public function log(string $action, ?string $modelType = null, ?int $modelId = null, ?array $changes = null): AuditLog
    {
        return $this->repository->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'changes' => $changes,
            'ip_address' => request()->ip(),
        ]);
    }
}
