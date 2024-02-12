<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Town extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getTravelDestinations()
    {
        $currentId = $this->id;
        $routes = Route::where(function ($query) use ($currentId) {
            $query->where('start_town_id', $currentId)
                ->orWhere('end_town_id', $currentId);
        })
            ->get();

        return $routes->map(function ($route) use ($currentId) {
            return [
                'town' => Town::find($route->start_town_id == $currentId
                    ? $route->end_town_id
                    : $route->start_town_id),
                'fuel_cost' => $route->fuel_cost,
            ];
        });
    }

    public function trainRefuel(Player $player, $cost)
    {
        $max_fuel = $player->train->locomotive->max_fuel;
        if ($cost <= $player->money) {
            $player->train->locomotive->update(['fuel' => $max_fuel]);
            $player->update(['money' => ($player->money - $cost)]);
            return true;
        } else {
            return false;
        }
    }

    /*
     * Relations
     */

    public function routesFrom(): HasMany
    {
        return $this->hasMany(Route::class, 'start_town_id');
    }

    public function routesTo(): HasMany
    {
        return $this->hasMany(Route::class, 'end_town_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'current_town_id');
    }
}
