<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property $name City name
 * @property $outgoingRoutes Outgoing routes
 * @property $players Players
 * @property $has_worksop Does the city have worksop?
 */
class City extends Model
{
    protected $fillable = [
        'name',
    ];

    public function outgoingRoutes(): HasMany
    {
        return $this->hasMany(CityRoute::class, 'from_city_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}
