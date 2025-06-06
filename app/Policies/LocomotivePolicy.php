<?php

namespace App\Policies;

use App\Models\Locomotive;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LocomotivePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Locomotive $locomotive): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function upgrade(User $user, Locomotive $locomotive): bool
    {
        return optional($user->player->train->locomotive)->is($locomotive);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Locomotive $locomotive): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Locomotive $locomotive): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Locomotive $locomotive): bool
    {
        return false;
    }
}
