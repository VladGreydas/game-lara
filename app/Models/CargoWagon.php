<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property $id
 * @property $wagon_id
 * @property Wagon $wagon
 * @property $capacity Cargo wagon capacity
 * @property Collection<int, CargoWagonResource> $resources All the resources in the wagon
 * @property-read int|null $resources_count
 * @property-read int $current_capacity
 * @property-read int $remaining_capacity
 */
class CargoWagon extends Model
{
    protected $fillable = ['wagon_id', 'capacity'];

    public function incrementEach(array $fields, int $lvl): void
    {
        foreach ($fields as $field => $valuePerLvl) {
            $this->increment($field, $valuePerLvl * $lvl);
        }
    }

    public function wagon(): BelongsTo
    {
        return $this->belongsTo(Wagon::class);
    }

    /**
     * Get the resources that are in the cargo wagon.
     */
    public function resources(): HasMany // Змінено на HasMany з CargoWagonResource
    {
        return $this->hasMany(CargoWagonResource::class);
    }

    /**
     * Get the current occupied capacity of the wagon.
     */
    public function getCurrentCapacity(): int
    {
        return $this->resources->sum('quantity');
    }

    /**
     * Get the remaining capacity of the wagon.
     */
    public function getRemainingCapacity(): int
    {
        return $this->capacity - $this->getCurrentCapacity();
    }
}
