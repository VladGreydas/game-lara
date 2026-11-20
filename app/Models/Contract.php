<?php

namespace App\Models;

use App\Services\LevelingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $city_id
 * @property int $destination_city_id
 * @property int $resource_id
 * @property int $quantity
 * @property int $reward
 * @property string $status
 * @property int|null $player_id
 * @property Carbon|null $accepted_at
 * @property Carbon|null $completed_at
 * @property-read City $city
 * @property-read City $destinationCity
 * @property-read Resource $resource
 * @property-read Player|null $player
 */
class Contract extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_COMPLETED = 'completed';

    public const MIN_QUANTITY = 5;
    public const MAX_QUANTITY = 20;

    protected $fillable = [
        'city_id',
        'destination_city_id',
        'resource_id',
        'quantity',
        'reward',
        'status',
        'player_id',
        'accepted_at',
        'completed_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * The saloon city offering this contract.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * The town the resource must be delivered to.
     */
    public function destinationCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Make sure a saloon city's contract board has enough available contracts,
     * generating new random "deliver X resource to Y town" offers if needed.
     * Count depends on city level: 3 + level²/10
     */
    public static function generateForCity(City $city): void
    {
        $count = 3 + (int) floor($city->level * $city->level / 10);

        $existingAvailable = self::where('city_id', $city->id)
            ->where('status', self::STATUS_AVAILABLE)
            ->count();

        if ($existingAvailable >= $count) {
            return;
        }

        $resources = \Illuminate\Support\Facades\Cache::remember('resources.all', 3600, function () {
            return Resource::all();
        });
        $destinationCities = \Illuminate\Support\Facades\Cache::remember('cities.all', 3600, function () {
            return City::all();
        })->where('id', '!=', $city->id);

        if ($resources->isEmpty() || $destinationCities->isEmpty()) {
            return;
        }

        for ($i = $existingAvailable; $i < $count; $i++) {
            $resource = $resources->random();
            $destinationCity = $destinationCities->random();
            $quantity = random_int(self::MIN_QUANTITY, self::MAX_QUANTITY);
            $reward = (int) round($resource->base_price * $quantity * (random_int(150, 250) / 100));

            self::create([
                'city_id' => $city->id,
                'destination_city_id' => $destinationCity->id,
                'resource_id' => $resource->id,
                'quantity' => $quantity,
                'reward' => $reward,
                'status' => self::STATUS_AVAILABLE,
            ]);
        }
    }

    /**
     * Accept the contract on behalf of the given player.
     */
    public function accept(Player $player): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $this->status = self::STATUS_ACCEPTED;
        $this->player_id = $player->id;
        $this->accepted_at = now();
        $this->save();

        return true;
    }

    /**
     * How much of the contract's resource the player currently carries in cargo wagons.
     */
    public function getCarriedQuantity(Player $player): int
    {
        if (!$player->train) {
            return 0;
        }

        $cargoWagonIds = $player->train->wagons()
            ->where('type', 'cargo')
            ->with('cargo_wagon')
            ->get()
            ->pluck('cargo_wagon.id')
            ->filter();

        if ($cargoWagonIds->isEmpty()) {
            return 0;
        }

        return (int) CargoWagonResource::whereIn('cargo_wagon_id', $cargoWagonIds)
            ->where('resource_id', $this->resource_id)
            ->sum('quantity');
    }

    /**
     * Whether the given player can currently deliver this contract.
     */
    public function canBeDeliveredBy(Player $player): bool
    {
        return $this->isAccepted()
            && $this->player_id === $player->id
            && !$player->isTraveling()
            && $player->city_id === $this->destination_city_id
            && $this->getCarriedQuantity($player) >= $this->quantity;
    }

    /**
     * Deliver the contract: remove the resource from the player's cargo wagons,
     * pay the reward, and mark the contract as completed.
     */
    public function deliverBy(Player $player): bool
    {
        if (!$this->canBeDeliveredBy($player)) {
            return false;
        }

        $remaining = $this->quantity;

        $cargoWagons = $player->train->wagons()
            ->where('type', 'cargo')
            ->with('cargo_wagon.resources')
            ->get()
            ->pluck('cargo_wagon')
            ->filter();

        foreach ($cargoWagons as $cargoWagon) {
            if ($remaining <= 0) {
                break;
            }

            $cargoWagonResource = $cargoWagon->resources->firstWhere('resource_id', $this->resource_id);
            if (!$cargoWagonResource || $cargoWagonResource->quantity <= 0) {
                continue;
            }

            $toRemove = min($remaining, $cargoWagonResource->quantity);
            $cargoWagonResource->quantity -= $toRemove;

            if ($cargoWagonResource->quantity <= 0) {
                $cargoWagonResource->delete();
            } else {
                $cargoWagonResource->save();
            }

            $remaining -= $toRemove;
        }

        if ($remaining > 0) {
            return false;
        }

        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        $this->save();

        $player->addMoney($this->reward);

        $expAmount = max(10, (int) floor($this->reward / 2));
        app(LevelingService::class)->addExperience($player, $expAmount, 'contract');

        return true;
    }
}
