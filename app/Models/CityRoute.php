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
 * @property int $fuel_cost
 * @property int $travel_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\City|null $fromCity
 * @property-read \App\Models\City|null $toCity
 * @property-read \App\Models\Location|null $fromLocation
 * @property-read \App\Models\Location|null $toLocation
 */
class CityRoute extends Model
{
    protected $fillable = [
        'from_city_id',
        'to_city_id',
        'from_location_id',
        'to_location_id',
        'fuel_cost',
        'travel_time',
    ];

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

    public function isAvailableFrom(int $fromId, ?string $fromType = 'city'): bool
    {
        return $this->from_id === $fromId && str_starts_with($this->type, $fromType . '_');
    }
}
