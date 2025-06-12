<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $from_city_id
 * @property int $to_city_id
 * @property int $fuel_cost
 * @property int $travel_time // NEW: Add this property
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\City $fromCity
 * @property-read \App\Models\City $toCity
 */
class CityRoute extends Model
{
    protected $fillable = [
        'from_city_id',
        'to_city_id',
        'fuel_cost',
        'travel_time', // NEW: Add this
    ];

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'from_city_id');
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'to_city_id');
    }

    public function isAvailableFrom(int $cityId): bool
    {
        return $this->from_city_id === $cityId;
    }
}
