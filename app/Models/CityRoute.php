<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $from_id
 * @property int $to_id
 * @property string $type
 * @property int $distance_km
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CityRoute extends Model
{
    protected $fillable = [
        'from_id',
        'to_id',
        'type',
        'distance_km',
    ];

    protected $types= [
        'city_to_city',
        'city_to_location',
        'location_to_city',
    ];

    public function isCityToCity()
    {
        return $this->type === 'city_to_city';
    }

    public function isLocationToCity()
    {
        return $this->type === 'location_to_city';
    }

    public function isCityToLocation()
    {
        return $this->type === 'city_to_location';
    }

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'from_id');
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'to_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_id');
    }

    public function isAvailableFrom(int $fromId, ?string $fromType = 'city_to_city'): bool
    {
        return $this->from_id === $fromId && in_array($fromType, $this->types);
    }
}
