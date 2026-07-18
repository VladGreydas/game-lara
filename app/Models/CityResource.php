<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Додайте цей імпорт
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $city_id
 * @property int $resource_id
 * @property int $quantity
 * @property int $base_quantity
 * @property float $price_multiplier
 * @property float $buy_price
 * @property float $sell_price
 * @property bool $is_surplus
 * @property bool $is_deficit
 * @property int $level
 * @property-read City $city
 * @property-read Resource $resource
 */
class CityResource extends Model
{
    use HasFactory;

    protected $table = 'city_resources'; // Явно вказуємо назву таблиці

    protected $fillable = [
        'city_id',
        'resource_id',
        'quantity',
        'base_quantity',
        'price_multiplier',
        'buy_price',
        'sell_price',
        'is_surplus',
        'is_deficit',
        'level',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (CityResource $cityResource) {
            $cityResource->updatePriceMultiplier();
        });
    }

    /**
     * Calculate and update the price_multiplier based on quantity, base_quantity, and level.
     * This method will be called automatically before saving the model.
     */
    public function updatePriceMultiplier(): void
    {
        if ($this->base_quantity <= 0) {
            $this->price_multiplier = 1.0;
            $this->is_surplus = false;
            $this->is_deficit = false;
            return;
        }

        $deviation = ($this->quantity - $this->base_quantity) / $this->base_quantity;
        $maxMultiplier = 2.0;
        $minMultiplier = 0.5;
        $levelFactor = 1.0 - ($this->level * 0.05);
        $calculatedMultiplier = 1.0 - ($deviation * 0.5 * $levelFactor);

        $this->price_multiplier = max($minMultiplier, min($maxMultiplier, $calculatedMultiplier));

        // ✅ Оновлено: визначаємо surplus/deficit на основі множника
        $this->is_surplus = $this->price_multiplier > 1.0;
        $this->is_deficit = $this->price_multiplier < 1.0;
    }

    /**
     * Check if the resource is in surplus in this city.
     *
     * @return bool
     */
    public function isSurplus(): bool
    {
        return $this->price_multiplier > 1.0;
    }

    /**
     * Check if the resource is in deficit in this city.
     *
     * @return bool
     */
    public function isDeficit(): bool
    {
        return $this->price_multiplier < 1.0;
    }

    /**
     * Get the current buy price of the resource in this city.
     *
     * @return float
     */
    public function getCurrentBuyPrice(): float
    {
        return round($this->buy_price * $this->price_multiplier, 2);
    }

    /**
     * Get the current sell price of the resource in this city.
     *
     * @return float
     */
    public function getCurrentSellPrice(): float
    {
        return round($this->sell_price * $this->price_multiplier, 2);
    }

    /**
     * Upgrade the resource level if player can afford it.
     *
     * @return bool
     */
    public function upgrade(): bool
    {
        $cost = $this->getUpgradeCost();
        $player = $this->city->players()->first();

        if (!$player || !$player->checkIfEnough($cost)) {
            return false;
        }

        $player->addMoney(-$cost);
        if ($this->level < 10) {
            $this->level++;
            $this->save();
            $this->updatePriceMultiplier();
        }

        return true;
    }

    /**
     * Calculate upgrade cost based on current level and resource ID.
     *
     * @return int
     */
    public function getUpgradeCost(): int
    {
        return 500 * $this->level;// * $this->resource_id;
    }

    /**
     * Get the city that owns the resource record.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the resource definition.
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
