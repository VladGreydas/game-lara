<?php

namespace App\Services;

use App\Models\CityRoute;
use App\Models\Player;
use Illuminate\Support\Facades\Log;

class TravelService
{
    public function processTravels(): void
    {
        $arrivedPlayers = Player::whereNotNull('current_city_route_id')
            ->where('travel_finishes_at', '<=', now())
            ->get();

        foreach ($arrivedPlayers as $player) {
            $route = $player->currentCityRoute;
            if ($route->isCityToCity() || $route->isLocationToCity()) {
                $player->city_id = $route->toCity->id;
                $player->current_location_id = null;
            } elseif ($route->isCityToLocation() || $route->isLocationToLocation()) {
                $player->current_location_id = $route->toLocation->id;
                $player->city_id = null;
            }

            $player->current_city_route_id = null;
            $player->travel_starts_at = null;
            $player->travel_finishes_at = null;
            $player->save();
        }
    }
}
