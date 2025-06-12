<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $type
 * @property int $travel_time
 * @property int $travel_cost
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LocationResource> $resources
 */
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'travel_time',
        'travel_cost',
    ];

    /**
     * Get the resources available at this location.
     */
    public function resources(): HasMany
    {
        return $this->hasMany(LocationResource::class);
    }

    // Автоматичне генерування slug перед збереженням
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($location) {
            $location->slug = Str::slug($location->name);
        });
        static::updating(function ($location) {
            if ($location->isDirty('name')) {
                $location->slug = Str::slug($location->name);
            }
        });
    }
}
