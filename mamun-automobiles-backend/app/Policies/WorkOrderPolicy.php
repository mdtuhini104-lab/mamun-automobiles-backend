<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('work_orders.view', 'web');
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('work_orders.view', 'web');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('work_orders.create', 'web');
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('work_orders.edit', 'web');
    }

    public function addConsumption(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('work_orders.edit', 'web');
    }

    public function close(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('work_orders.close', 'web');
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('work_orders.delete', 'web');
    }
}
