<?php

namespace App\Models;

use App\Services\PlayerSetupService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\RedirectResponse;

/**
 * @property int $id
 * @property int $user_id
 * @property int $city_id
 * @property int|null $current_location_id
 * @property int $money
 * @property string $nickname
 * @property int $lvl
 * @property int $exp
 * @property int $max_exp
 * @property int $stamina
 * @property int $max_stamina
 * @property Carbon|null $last_stamina_regenerated_at
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

    public const SCOUT_STAMINA_COST = 5;
    public const STAMINA_RECOVERY_INTERVAL_SECONDS = 60;

    protected $attributes = [
        'stamina' => 100,
        'max_stamina' => 100,
    ];

    protected $fillable = [
        'user_id',
        'nickname',
        'money',
        'exp',
        'max_exp',
        'lvl',
        'stamina',
        'max_stamina',
        'last_stamina_regenerated_at',
        'city_id',
        'current_city_route_id', // Додайте це поле, якщо воно буде використовуватися для подорожей
        'travel_starts_at',      // Час початку подорожі
        'travel_finishes_at',    // Час закінчення подорожі
        'current_location_id', // Додайте це поле
    ];

    protected $casts = [
        'travel_starts_at' => 'datetime',
        'travel_finishes_at' => 'datetime',
        'last_stamina_regenerated_at' => 'datetime',
    ];

    //Methods

    protected static function booted(): void
    {
        static::creating(function (Player $player) {
            if ($player->last_stamina_regenerated_at === null) {
                $player->last_stamina_regenerated_at = now();
            }
        });

        static::created(function (Player $player) {
            app(PlayerSetupService::class)->setupInitialAssets($player);
        });
    }

    public function regenerateStamina(): void
    {
        if ($this->stamina >= $this->max_stamina) {
            if ($this->stamina > $this->max_stamina) {
                $this->stamina = $this->max_stamina;
                $this->save();
            }
            return;
        }

        $lastRegen = $this->last_stamina_regenerated_at
            ? Carbon::parse($this->last_stamina_regenerated_at)
            : ($this->created_at ? Carbon::parse($this->created_at) : now());

        $secondsPassed = $lastRegen->diffInSeconds(now());
        $recoveryInterval = self::STAMINA_RECOVERY_INTERVAL_SECONDS;
        $pointsToRegen = (int) floor($secondsPassed / $recoveryInterval);

        if ($pointsToRegen > 0) {
            $this->stamina = min($this->max_stamina, $this->stamina + $pointsToRegen);

            if ($this->stamina >= $this->max_stamina) {
                $this->last_stamina_regenerated_at = now();
            } else {
                $this->last_stamina_regenerated_at = $lastRegen->copy()->addSeconds($pointsToRegen * $recoveryInterval);
            }

            $this->save();
        }
    }

    public function hasEnoughStamina(int $amount = self::SCOUT_STAMINA_COST): bool
    {
        $this->regenerateStamina();

        return $this->stamina >= $amount;
    }

    public function consumeStamina(int $amount): bool
    {
        $this->regenerateStamina();

        if ($this->stamina < $amount) {
            return false;
        }

        $wasFull = ($this->stamina >= $this->max_stamina);
        $this->stamina -= $amount;

        if ($wasFull || $this->last_stamina_regenerated_at === null) {
            $this->last_stamina_regenerated_at = now();
        }

        $this->save();

        return true;
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

    /**
     * Get the proficient resource for the player's current city or location.
     */
    public function getProficientResource(): ?Resource
    {
        if ($this->city) {
            $cityResource = $this->city->relationLoaded('resources')
                ? ($this->city->resources->firstWhere('is_surplus', true) ?? $this->city->resources->first())
                : ($this->city->resources()->where('is_surplus', true)->first() ?? $this->city->resources()->first());

            return $cityResource?->resource;
        }

        if ($this->currentLocation) {
            $locationResource = $this->currentLocation->relationLoaded('location_resource')
                ? $this->currentLocation->location_resource
                : $this->currentLocation->location_resource()->first();

            return $locationResource?->resource;
        }

        return null;
    }

    /**
     * Scout for resources and put them into player's available cargo wagons.
     *
     * @param int|null $amount
     * @return bool
     */
    public function scout(?int $amount = null): bool
    {
        $this->regenerateStamina();

        if ($this->stamina < self::SCOUT_STAMINA_COST) {
            return false;
        }

        $resource = $this->getProficientResource();

        if (!$resource) {
            return false;
        }

        if (!$this->train) {
            return false;
        }

        $cargoWagons = $this->train->wagons()
            ->where('type', 'cargo')
            ->with(['cargo_wagon.resources'])
            ->get();

        if ($cargoWagons->isEmpty()) {
            return false;
        }

        $totalFreeSpace = $cargoWagons->sum(function ($wagon) {
            return $wagon->cargo_wagon ? $wagon->cargo_wagon->getRemainingCapacity() : 0;
        });

        if ($totalFreeSpace <= 0) {
            return false;
        }

        $amountToScout = $amount ?? random_int(1, 10);
        $remaining = $amountToScout;
        $totalAdded = 0;

        foreach ($cargoWagons as $wagon) {
            if ($remaining <= 0) {
                break;
            }

            $cargoWagon = $wagon->cargo_wagon;
            if (!$cargoWagon) {
                continue;
            }

            $freeSpace = $cargoWagon->getRemainingCapacity();
            if ($freeSpace > 0) {
                $toAdd = min($remaining, $freeSpace);

                $cargoWagonResource = CargoWagonResource::firstOrNew([
                    'cargo_wagon_id' => $cargoWagon->id,
                    'resource_id' => $resource->id,
                ]);

                $cargoWagonResource->quantity = ($cargoWagonResource->quantity ?? 0) + $toAdd;
                $cargoWagonResource->save();

                $remaining -= $toAdd;
                $totalAdded += $toAdd;
            }
        }

        if ($totalAdded > 0) {
            $this->consumeStamina(self::SCOUT_STAMINA_COST);
            return true;
        }

        return false;
    }

    // NEW: Helper method to process arrival
    public function processArrival(): RedirectResponse
    {
        $route = $this->currentCityRoute;

        if (!$route) {
            $this->current_city_route_id = null;
            $this->travel_finishes_at = null;
            $this->save();
            return redirect()->route('player.index')->with('error', 'Travel state error.');
        }

        if ($route->isCityToCity() || $route->isLocationToCity()) {
            $this->city_id = $route->toCity->id;
            $this->current_location_id = null;
        } elseif ($route->isCityToLocation()) {
            $this->current_location_id = $route->toLocation->id;
            $this->city_id = null;
        }

        $this->current_city_route_id = null;
        $this->travel_finishes_at = null;
        $this->save();

        if ($this->city_id) {
            return redirect()->route('city.show', $this->city)->with('success', 'You have arrived at ' . $this->city->name);
        } else {
            return redirect()->route('locations.show', $this->currentLocation)->with('success', 'You have arrived at ' . $this->currentLocation->name);
        }
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
