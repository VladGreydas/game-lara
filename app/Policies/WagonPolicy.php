<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wagon;

class WagonPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Wagon $wagon): bool
    {
        return $user->player?->train->wagons->contains($wagon);
    }
}
