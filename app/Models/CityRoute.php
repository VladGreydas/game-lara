<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $from_id
 * @property int $to_id
 * @property string $type
 * @property int $fuel_cost
 * @property int $travel_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CityRoute extends Model
{
    protected $fillable = [
        'from_id',
        'to_id',
        'type',
        'fuel_cost',
        'travel_time',
    ];

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

    public function isAvailableFrom(int $fromId, ?string $fromType = 'city'): bool
    {
        return $this->from_id === $fromId && str_starts_with($this->type, $fromType . '_');
    }
}
