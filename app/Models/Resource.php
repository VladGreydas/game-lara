<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $unit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CargoWagonResource> $cargoWagons cargo wagons that have this resource
 * @property-read int|null $cargo_wagons_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CityResource> $cities
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
