<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property $name City name
 * @property $outgoingRoutes Outgoing routes
 * @property $players Players
 * @property $has_worksop Does the city have worksop?
 * @property $has_shop Does the city have a shop?
 * @property-read Collection<int, CityResource> $resources
 * @property-read int|null $resources_count
 */
class City extends Model
{
    protected $fillable = [
        'name',
    ];

    protected static function booted()
    {
        static::creating(function ($city) {
            $city->slug = Str::slug($city->name);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function outgoingRoutes(): HasMany
    {
        return $this->hasMany(CityRoute::class, 'from_city_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Get the resources available in the city.
     */
    public function resources(): HasMany // Змінено на HasMany з CityResource
    {
        return $this->hasMany(CityResource::class);
    }
}
