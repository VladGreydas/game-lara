<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $unit
 * @property bool $is_fuel
 * @property int $fuel_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, CargoWagonResource> $cargoWagons cargo wagons that have this resource
 * @property-read int|null $cargo_wagons_count
 * @property-read Collection<int, CityResource> $cities
 * @property-read int|null $cities_count
 */
class Resource extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'unit',
        'is_fuel',
        'fuel_value'
    ];

    /**
     * Get the cargo wagons that have this resource.
     */
    public function cargoWagons(): HasMany
    {
        return $this->hasMany(CargoWagonResource::class);
    }

    /**
     * Get the cities that have this resource.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(CityResource::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(LocationResource::class);
    }
}
