<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Weapon;

class WeaponPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Weapon $weapon): bool
    {
        return $user->player?->train->wagons->contains(function ($wagon) use ($weapon) {
            return $wagon->weapon_wagon?->weapons->contains($weapon);
        });
    }
}
