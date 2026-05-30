<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Purchase;

class PurchasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('purchases.view', 'web');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Purchase $purchase): bool
    {
        return $user->hasPermissionTo('purchases.view', 'web');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('purchases.create', 'web');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Purchase $purchase): bool
    {
        return $user->hasPermissionTo('purchases.edit', 'web');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Purchase $purchase): bool
    {
        return $user->hasPermissionTo('purchases.delete', 'web');
    }
}
