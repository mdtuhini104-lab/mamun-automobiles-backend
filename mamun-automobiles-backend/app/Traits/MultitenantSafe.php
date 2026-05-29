<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait MultitenantSafe
{
    /**
     * Boot the trait to apply tenant scoping and saving.
     */
    public static function bootMultitenantSafe(): void
    {
        static::creating(function (Model $model) {
            if (auth()->check() && !$model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });

        static::addGlobalScope('tenant_scope', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                // Bypass scope only for system super-admin users
                if ($user->hasRole('Super Admin')) {
                    return;
                }
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $user->tenant_id);
            }
        });
    }
}
