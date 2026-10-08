<?php

namespace App\Models;

use App\Models\CargoWagonResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property int $location_id
 * @property int $resource_id
 * @property float $production_rate_per_hour
 * @property int $max_capacity
 * @property int $current_amount
 * @property \Illuminate\Support\Carbon|null $last_produced_at
 */
class LocationResource extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_id',
        'resource_id',
        'production_rate_per_hour',
        'max_capacity',
        'current_amount',
        'last_produced_at'
    ];

    /**
     * Get the location that owns the resource.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the resource that is produced at this location.
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Збирає ресурси для гравця та додає їх у вільний вагон.
     *
     * @param \App\Models\Player $player
     * @return bool
     */
    public function collectForPlayer(\App\Models\Player $player): bool
    {
        Log::info('LocationResource::collectForPlayer() викликано', [
            'location_resource_id' => $this->id,
            'current_amount' => $this->current_amount,
            'production_rate_per_hour' => $this->production_rate_per_hour,
            'last_produced_at' => Carbon::parse($this->last_produced_at),
        ]);

        if ($this->current_amount <= 0) {
            Log::info('LocationResource::collectForPlayer() — current_amount <= 0, перериваємо.');
            return false;
        }

        $this->produce();

        if ($this->current_amount <= 0) {
            Log::info('LocationResource::collectForPlayer() — склади пусті, перериваємо.');
            return false;
        }

        $train = $player->train;
        if (!$train) {
            Log::info('LocationResource::collectForPlayer() — у гравця немає потяга.');
            return false;
        }

        $cargoWagons = $train->wagons->where('type', '=', 'cargo');

        if (!$cargoWagons) {
            Log::info('LocationResource::collectForPlayer() — немає вантажних вагонів.');
            return false;
        }

        $loaded = 0;

        foreach ($cargoWagons as $cargoWagon) {
            if ($cargoWagon->cargo_wagon->getRemainingCapacity() > 0) {
                $cargoWagonResource = CargoWagonResource::firstOrNew([
                    'cargo_wagon_id' => $cargoWagon->cargo_wagon->id,
                    'resource_id' => $this->resource_id,
                ]);

                $amountToLoad = min($cargoWagon->cargo_wagon->getRemainingCapacity(), $this->current_amount);

                $cargoWagonResource->quantity += $amountToLoad;
                $cargoWagonResource->save();

                $this->current_amount -= $amountToLoad;
                $this->last_produced_at = now();
                $this->save(); // Це зберігає зміни в БД

                Log::info('LocationResource::collectForPlayer() — успішно зібрано ресурси', [
                    'produced' => $amountToLoad,
                    'new_current_amount' => $this->current_amount,
                    'new_last_produced_at' => Carbon::parse($this->last_produced_at),
                ]);
                $loaded++;
            }
        }
        if ($loaded == 0) {
            Log::info('LocationResource::collectForPlayer() — немає вільних вантажних вагонів для завантаження.');
            return false;
        }
        return true;
    }

    public function produce()
    {
        $last = Carbon::parse($this->last_produced_at ?? now()->subHour());
        $diff = $last->diffInHours(now());
        $hours = max(0, $diff);
        $produced = (int) ($hours * $this->production_rate_per_hour);
        Log::info('LocationResource::produce() — розрахунок виробництва', [
            'hours' => $hours,
            'produced' => $produced,
        ]);
        $total = $produced + $this->current_amount;
        if ($total >= $this->max_capacity) $total = $this->max_capacity;
        $this->current_amount = $total;
        $this->last_produced_at = now();
        $this->save();
    }
}
