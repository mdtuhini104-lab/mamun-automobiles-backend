<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BranchScoped
{
    /**
     * Boot the trait to apply branch scoping and saving.
     */
    public static function bootBranchScoped(): void
    {
        static::creating(function (Model $model) {
            if (auth()->check() && !$model->branch_id) {
                $model->branch_id = config('app.current_branch_id') ?: auth()->user()->branch_id;
            }
        });

        static::addGlobalScope('branch_scope', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                // Bypass scope only for system super-admin users unless a specific branch is set in config
                if ($user->hasRole('Super Admin')) {
                    $branchId = config('app.current_branch_id');
                    if ($branchId) {
                        $builder->where($builder->getModel()->getTable() . '.branch_id', $branchId);
                    }
                    return;
                }

                $branchId = config('app.current_branch_id') ?: $user->branch_id;
                if ($branchId) {
                    $builder->where($builder->getModel()->getTable() . '.branch_id', $branchId);
                }
            }
        });
    }
}
