<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property $wagon_id Wagon ID
 * @property $slots_available Slots available
 * @property $weapons Weapons
 * @property $wagon Wagon
 */
class WeaponWagon extends Model
{
    protected $fillable = ['wagon_id', 'slots_available'];

    public function wagon(): BelongsTo
    {
        return $this->belongsTo(Wagon::class);
    }

    public function weapons(): HasMany
    {
        return $this->hasMany(Weapon::class);
    }
}
