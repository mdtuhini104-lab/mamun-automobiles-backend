<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public static function log(string $module, string $action, string $description, array $oldValues = null, array $newValues = null, string $severity = 'info')
    {
        $user = auth()->user();
        
        ActivityLog::create([
            'user_id' => $user?->id,
            'tenant_id' => $user?->tenant_id ?? null,
            'branch_id' => $user?->branch_id ?? null,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'severity' => $severity,
        ]);
    }
}
