<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CityRoute extends Model
{
    protected $fillable = ['from_city_id', 'to_city_id', 'fuel_cost'];

    public function isAvailableFrom(int $cityId): bool
    {
        return $this->from_city_id === $cityId;
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'to_city_id');
    }
}
