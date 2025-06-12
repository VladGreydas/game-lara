<?php

namespace App\Models;

use App\Services\PlayerSetupService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $user_id
 * @property int $city_id
 * @property int|null $current_location_id
 * @property int $money
 * @property string $nickname
 * @property int $lvl
 * @property int $experience
 * @property int $hp
 * @property int $max_hp
 * @property int $current_city_route_id
 * @property Carbon $travel_starts_at
 * @property Carbon $travel_finishes_at
 * @property-read User $user
 * @property-read City $city
 * @property-read Location|null $currentLocation
 * @property-read Train $train
 */
class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'nickname',
        'money',
        'exp',
        'max_exp',
        'lvl',
        'city_id',
        'current_city_route_id', // Додайте це поле, якщо воно буде використовуватися для подорожей
        'travel_starts_at',      // Час початку подорожі
        'travel_finishes_at',    // Час закінчення подорожі
        'current_location_id', // Додайте це поле
    ];

    protected $casts = [
        'travel_starts_at' => 'datetime',
        'travel_finishes_at' => 'datetime',
    ];

    //Methods

    protected static function booted(): void
    {
        static::created(function (Player $player) {
            app(PlayerSetupService::class)->setupInitialAssets($player);
        });
    }

    public function checkIfEnough($cost)
    {
        return $this->money >= $cost;
    }

    public function addMoney($money)
    {
        if ($money > 0) {
            $this->update(['money' => $this->money + $money]);
        }
    }

    public function canLevelUp(): bool
    {
        return $this->exp >= $this->max_exp;
    }

    //Relations

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function train() : HasOne
    {
        return $this->hasOne(Train::class);
    }

    public function city() : BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    // Зв'язок для поточного маршруту, якщо гравець у подорожі
    public function currentCityRoute(): BelongsTo
    {
        return $this->belongsTo(CityRoute::class, 'current_city_route_id');
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    /**
     * Check if the player is currently traveling.
     */
    public function isTraveling(): bool
    {
        // Перевіряємо, чи є маршрут подорожі та чи не закінчилася подорож
        return $this->current_city_route_id !== null && $this->travel_finishes_at && $this->travel_finishes_at->isFuture();
    }

    /**
     * Check if the player is currently in a city.
     * (Ви могли б мати isStationary(), якщо він може бути і в локаціях, і в містах)
     */
    public function inCity(): bool
    {
        // Гравець у місті, якщо немає активного маршруту подорожі
        // і він має city_id (тобто не в "ніде")
        return $this->city_id !== null && !$this->isTraveling();
    }

    /**
     * Check if the player's train has arrived at its destination.
     */
    public function hasArrived(): bool
    {
        // Player has arrived if travel_finishes_at is set AND it's in the past
        return $this->travel_finishes_at && $this->travel_finishes_at->isPast();
    }
}
