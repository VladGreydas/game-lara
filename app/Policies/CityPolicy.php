<?php

namespace App\Policies;

use App\Models\City;
use App\Models\Player;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CityPolicy
{
    /**
     * Determine if the player can upgrade the city.
     *
     * @param  \App\Models\Player  $player
     * @param  \App\Models\City  $city
     * @return bool
     */
    public function upgrade(Player $player, City $city): bool
    {
        // Гравець може покращувати лише міста, в яких він перебуває
        if ($player->city_id !== $city->id) {
            return false;
        }

        // Покращення не можна виконувати під час подорожі
        if ($player->isTraveling()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can upgrade the city.
     * Це перевантаження для використання в контролері через authorize()
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\City  $city
     * @return bool
     */
    public function upgradeForUser(User $user, City $city): bool
    {
        $player = $user->player;
        return $player && $this->upgrade($player, $city);
    }

    /**
     * Determine if the player can manage city resources.
     *
     * @param  \App\Models\Player  $player
     * @param  \App\Models\City  $city
     * @return bool
     */
    public function manageResources(Player $player, City $city): bool
    {
        // Гравець може керувати ресурсами лише в місті, де перебуває
        return $player->city_id === $city->id && !$player->isTraveling();
    }

    /**
     * Determine if the player can access the city workshop.
     *
     * @param  \App\Models\Player  $player
     * @param  \App\Models\City  $city
     * @return bool
     */
    public function accessWorkshop(Player $player, City $city): bool
    {
        return $city->has_workshop && $player->city_id === $city->id && !$player->isTraveling();
    }

    /**
     * Determine if the player can access the city shop.
     *
     * @param  \App\Models\Player  $player
     * @param  \App\Models\City  $city
     * @return bool
     */
    public function accessShop(Player $player, City $city): bool
    {
        return $city->has_shop && $player->city_id === $city->id && !$player->isTraveling();
    }
}
