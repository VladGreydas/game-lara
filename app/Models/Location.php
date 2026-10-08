<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

/**
 * @property int|null $city_id
 * @property string $name
 * @property string $type
 * @property string slug
 * @property LocationResource location_resource
*/
class Location extends Model
{
    protected $fillable = [
        'city_id',
        'name',
        'type',
        'slug'
    ];

    public function getOutgoingRoute()
    {
        return CityRoute::where('from_location_id', $this->id)->where('type', 'location_to_city')->first();
    }

    /**
     * Заправити локомотив з локації (Доступно лише для вугільних шахт)
     *
     * @param Player $player
     * @return bool
     */
    public function refuel(Player $player): bool
    {
        $locomotive = $player->train->locomotive;

        if (!$locomotive) {
            Log::info('Location::refuel() — locomotive does not exist.');
            return false;
        }

        if ($locomotive->fuel >= $locomotive->max_fuel) {
            Log::info('Location::refuel() - locomotive have enough fuel.');
            return false;
        }

        $location_resource = $this->location_resource;

        if ($locomotive->getFuelType() != $location_resource->resource->slug) {
            Log::info('Location::refuel() - locomotive does not match resource type.');
            return false;
        }

        if ($location_resource->current_amount == 0) {
            Log::info('Location::refuel() - don`t have any fuel.');
            return false;
        }

        $amount_to_refuel = $locomotive->max_fuel - $locomotive->fuel;

        $location_resource->current_amount -= $amount_to_refuel;
        $location_resource->save();

        $locomotive->fuel = $locomotive->max_fuel;
        $locomotive->save();

        return true;
    }

    // Relations

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'location_id');
    }

    public function location_resource(): HasOne
    {
        return $this->hasOne(LocationResource::class, 'location_id');
    }
}
