<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property $name City name
 * @property $level City level
 * @property $max_level Max city level
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
        'level',
        'max_level',
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

    // Relations

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
    public function resources(): HasMany
    {
        return $this->hasMany(CityResource::class);
    }

    /**
     * Upgrade the city level if player can afford it.
     *
     * @return bool
     */
    public function upgrade(): bool
    {
        $cost = $this->getUpgradeCost();
        $player = $this->players()->first();

        if (!$player || !$player->checkIfEnough($cost)) {
            return false;
        }

        $player->addMoney(-$cost);
        if ($this->level < $this->max_level) {
            $this->level++;
            $this->save();
            $this->refreshResourcesCaps();
        }

        return true;
    }

    /**
     * Calculate upgrade cost based on current level.
     *
     * @return int
     */
    public function getUpgradeCost(): int
    {
        return 1000 * $this->level;
    }

    /**
     * Refresh base quantities for all city resources based on city level.
     *
     * @return void
     */
    protected function refreshResourcesCaps(): void
    {
        $this->resources()->update([
            'base_quantity' => 1000 + $this->level * 500,
        ]);
    }
}
