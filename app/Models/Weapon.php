<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property $weapon_wagon_id Weapon wagon ID
 * @property $name Weapon name
 * @property $damage Weapon damage
 * @property $price Weapon price
 * @property $lvl Weapon level
 * @property $upgrade_cost Weapon upgrade cost
 */
class Weapon extends Model
{
    protected $fillable = [
        'weapon_wagon_id',
        'name',
        'damage',
        'type',
        'price',
        'lvl',
        'upgrade_cost'
    ];

    public function weapon_wagon(): BelongsTo
    {
        return $this->belongsTo(WeaponWagon::class);
    }
}
