<?php

namespace App\Services;

use App\Models\CityRoute;
use App\Models\Player;
use Illuminate\Support\Facades\Log;

class TravelService
{
    public function processTravels(): void
    {
        // Знаходимо всі потяги, які позначені як "у дорозі" і чий час прибуття вже минув
        $arrivedPlayers = Player::where('city_id', null)
            ->where('travel_finishes_at', '<=', now())
            ->get();

        if ($arrivedPlayers->isEmpty()) {
            // Log::info('No players arrived this cycle.');
            return;
        }

        foreach ($arrivedPlayers as $player) {
            /* @var Player $player */
            // Використовуємо транзакцію для атомарності оновлень
            try {
                \DB::transaction(function () use ($player) {
                    // 1. Оновлення поточної локації гравця
                    $route = CityRoute::find($player->current_city_route_id);
                    $player->city_id = $route->to_city_id; // Місто прибуття
                    $player->save();

                    // 2. Очищення полів подорожі у потяга
                    $player->travel_starts_at = null;
                    $player->travel_finishes_at = null;
                    $player->current_city_route_id = null;
                    $player->save();

                    Log::info("Player: {$player->id} arrived at City #{$player->city_id}.");
                    // Можна додати Fire Event для нотифікації гравця
                });

            } catch (\Exception $e) {
                Log::error("Failed to process arrival for Player #{$player->id}: " . $e->getMessage());
            }
        }
    }
}
