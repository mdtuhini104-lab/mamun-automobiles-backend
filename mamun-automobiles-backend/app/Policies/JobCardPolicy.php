<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JobCard;

class JobCardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('job_cards.view', 'web');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobCard $jobCard): bool
    {
        return $user->hasPermissionTo('job_cards.view', 'web');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('job_cards.create', 'web');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobCard $jobCard): bool
    {
        return $user->hasPermissionTo('job_cards.edit', 'web');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobCard $jobCard): bool
    {
        return $user->hasPermissionTo('job_cards.delete', 'web');
    }
}
