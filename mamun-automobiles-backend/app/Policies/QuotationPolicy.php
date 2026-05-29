<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quotation;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('quotations.view', 'web');
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $user->hasPermissionTo('quotations.view', 'web');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('quotations.create', 'web');
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->hasPermissionTo('quotations.edit', 'web');
    }

    public function approve(User $user, Quotation $quotation): bool
    {
        return $user->hasPermissionTo('quotations.approve', 'web');
    }

    public function revise(User $user, Quotation $quotation): bool
    {
        return $user->hasPermissionTo('quotations.revise', 'web');
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->hasPermissionTo('quotations.delete', 'web');
    }
}
