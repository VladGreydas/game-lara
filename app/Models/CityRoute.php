<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $from_city_id
 * @property int|null $to_city_id
 * @property int|null $from_location_id
 * @property int|null $to_location_id
 * @property string $type
 * @property int $distance_km
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CityRoute extends Model
{
    protected $fillable = [
        'from_city_id',
        'to_city_id',
        'from_location_id',
        'to_location_id',
        'type',
        'distance_km',
    ];

    protected $types = [
        'city_to_city',
        'city_to_location',
        'location_to_city',
    ];

    public function getFrom()
    {
        return match ($this->type) {
            'city_to_city', 'city_to_location' => 'city',
            'location_to_city' => 'location',
        };
    }

    public function isCityToCity(): bool
    {
        return $this->type === 'city_to_city';
    }

    public function isLocationToCity(): bool
    {
        return $this->type === 'location_to_city';
    }

    public function isCityToLocation(): bool
    {
        return $this->type === 'city_to_location';
    }

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'from_city_id');
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'to_city_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function isAvailableFrom(int $fromId, ?string $fromType = 'city_to_city'): bool
    {
        return in_array($fromType, $this->types) && (
            ($this->from_city_id === $fromId && in_array($fromType, ['city_to_city', 'city_to_location'])) ||
            ($this->from_location_id === $fromId && in_array($fromType, ['location_to_city']))
        );
    }
}
