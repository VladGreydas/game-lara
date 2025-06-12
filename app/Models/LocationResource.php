<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $location_id
 * @property int $resource_id
 * @property int $initial_quantity
 * @property int $current_quantity
 * @property int $regeneration_rate
 * @property int $regeneration_interval
 * @property \Illuminate\Support\Carbon|null $last_regenerated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $location
 * @property-read \App\Models\Resource $resource
 */
class LocationResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'resource_id',
        'initial_quantity',
        'current_quantity',
        'regeneration_rate',
        'regeneration_interval',
        'last_regenerated_at',
    ];

    protected $casts = [
        'last_regenerated_at' => 'datetime',
    ];

    /**
     * Get the location that owns the LocationResource.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the resource that owns the LocationResource.
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Regenerate the resource quantity based on time elapsed.
     */
    public function regenerateQuantity(): void
    {
        if (is_null($this->last_regenerated_at)) {
            $this->last_regenerated_at = now();
            $this->save();
            return;
        }

        $minutesSinceLastRegen = $this->last_regenerated_at->diffInMinutes(now());
        $intervalsPassed = floor($minutesSinceLastRegen / $this->regeneration_interval);

        if ($intervalsPassed > 0) {
            $amountToRegenerate = $intervalsPassed * $this->regeneration_rate;
            $newQuantity = $this->current_quantity + $amountToRegenerate;
            $this->current_quantity = min($newQuantity, $this->initial_quantity); // Не перевищуємо початкову кількість
            $this->last_regenerated_at = $this->last_regenerated_at->addMinutes($intervalsPassed * $this->regeneration_interval);
            $this->save();
        }
    }
}
