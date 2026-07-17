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
 * @property $has_workshop Does the city have workshop?
 * @property $has_shop Does the city have a shop?
 * @property $has_saloon Does the city have a saloon?
 * @property-read Collection<int, CityResource> $resources
 * @property-read int|null $resources_count
 */
class City extends Model
{
    protected $fillable = [
        'name',
        'level',
        'max_level',
        'has_workshop',
        'has_shop',
        'has_saloon',
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
     * Applies discount if city has workshop.
     *
     * @return int
     */
    public function getUpgradeCost(): int
    {
        $baseCost = 1000 * $this->level;
        if ($this->has_workshop) {
            // 20% discount for cities with workshop
            return (int) round($baseCost * 0.8);
        }
        return $baseCost;
    }

    /**
     * Refresh base quantities for all city resources based on city level.
     * Only allows resource improvement if city has a shop.
     *
     * @return void
     */
    protected function refreshResourcesCaps(): void
    {
        if (!$this->has_shop) {
            return;
        }

        $this->resources()->update([
            'base_quantity' => 1000 + $this->level * 500,
        ]);
    }

    /**
     * Get discount rate for Workshop.
     * 10% * city level discount for repairs.
     * 5% * city level for upgrades
     * @param string $type - Upgrade/Repair
     * @return float
     */
    public function getWorkshopDiscount(string $type): float
    {
        return match ($type) {
            'repair' => ($this->level - 1) * 0.1,
            'upgrade' => ($this->level - 1) * 0.05
        };
    }

    /**
     * Get discount rate for purchases in Shop (Resources, Wagons, Locomotives, Weapons).
     * 10% discount if city has shop.
     *
     * @return float
     */
    public function getShopDiscount(): float
    {
        return ($this->level - 1) * 0.05;
    }

    /**
     * Check if the city has a saloon.
     *
     * @return bool
     */
    public function hasSaloon(): bool
    {
        return (bool) $this->has_saloon;
    }
}
