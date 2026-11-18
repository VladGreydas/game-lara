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

    // Quantity bounds to keep prices stable
    protected const MIN_QUANTITY = 100;
    protected const MAX_QUANTITY = 3000;

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
            $this->price_multiplier = 2.0;
            $this->is_surplus = false;
            $this->is_deficit = true;
            return;
        }

        $deviation = ($this->quantity - $this->base_quantity) / $this->base_quantity;
        $maxMultiplier = 2.0;
        $minMultiplier = 0.5;
        $levelFactor = 1.0 - ($this->level * 0.05);
        $calculatedMultiplier = 1.0 - ($deviation * 0.5 * $levelFactor);

        $this->price_multiplier = max($minMultiplier, min($maxMultiplier, $calculatedMultiplier));

        // ✅ Оновлено: визначаємо surplus/deficit на основі множника
        $this->is_surplus = $this->price_multiplier <= 0.75;
        $this->is_deficit = $this->price_multiplier >= 1.25;
    }

    /**
     * Check if the resource is in surplus in this city.
     *
     * @return bool
     */
    public function isSurplus(): bool
    {
        return $this->price_multiplier < 1.0;
    }

    /**
     * Check if the resource is in deficit in this city.
     *
     * @return bool
     */
    public function isDeficit(): bool
    {
        return $this->price_multiplier > 1.0;
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

    /**
     * Adjust quantity with bounds and update price multiplier.
     * This is the public entry point for ShopBuyService to change prices.
     *
     * @param int $delta Positive to add, negative to remove
     * @return int The actual quantity change applied (may be clamped)
     */
    public function adjustQuantity(int $delta): int
    {
        $newQuantity = $this->quantity + $delta;

        // Clamp to bounds
        if ($newQuantity < self::MIN_QUANTITY) {
            $newQuantity = self::MIN_QUANTITY;
        } elseif ($newQuantity > self::MAX_QUANTITY) {
            $newQuantity = self::MAX_QUANTITY;
        }

        $actualDelta = $newQuantity - $this->quantity;

        if ($actualDelta !== 0) {
            $this->quantity = $newQuantity;
            $this->updatePriceMultiplier();
            $this->save();
        }

        return $actualDelta;
    }

    /**
     * Get the price direction compared to base price.
     * Returns 'up' if price is higher, 'down' if lower, 'stable' if close to base.
     *
     * @return string 'up', 'down', or 'stable'
     */
    public function getPriceDirection(): string
    {
        if ($this->price_multiplier > 1.05) {
            return 'up';
        } elseif ($this->price_multiplier < 0.95) {
            return 'down';
        }
        return 'stable';
    }
}
