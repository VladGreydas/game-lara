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
     * Calculate and update the price_multiplier based on quantity and base_quantity.
     * This method will be called automatically before saving the model.
     */
    public function updatePriceMultiplier(): void
    {
        if ($this->base_quantity <= 0) {
            // Уникаємо ділення на нуль, якщо базова кількість не встановлена або 0
            $this->price_multiplier = 1.0;
            return;
        }

        // Обчислюємо відхилення від базової кількості у відсотках
        // Наприклад: (500 - 1000) / 1000 = -0.5 (дефіцит 50%)
        //           (1500 - 1000) / 1000 = 0.5 (профіцит 50%)
        $deviation = ($this->quantity - $this->base_quantity) / $this->base_quantity;

        // Визначаємо максимальний/мінімальний множник, щоб ціни не були абсурдними
        $maxMultiplier = 2.0; // Макс. ціна в 2 рази вище базової
        $minMultiplier = 0.5; // Мін. ціна в 2 рази нижче базової

        // Налаштовуємо чутливість: наприклад, -1.0 відхилення -> 1.5 множник, 1.0 відхилення -> 0.5 множник
        // Множник змінюється обернено пропорційно до відхилення:
        // Якщо відхилення -0.5, хочемо множник > 1.0 (дорожче)
        // Якщо відхилення 0.5, хочемо множник < 1.0 (дешевше)
        $calculatedMultiplier = 1.0 - ($deviation * 0.5); // 0.5 - це коефіцієнт чутливості

        // Обмежуємо множник, щоб він не виходив за встановлені межі
        $this->price_multiplier = max($minMultiplier, min($maxMultiplier, $calculatedMultiplier));
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
