<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property $train_id Train ID
 * @property $name Wagon name
 * @property $type Wagon type
 * @property $weight Wagon weight
 * @property $armor Wagon armor
 * @property $max_armor Wagon max armor
 * @property $lvl Wagon level
 * @property $price Wagon price
 * @property $upgrade_cost Wagon upgrade cost
 * @property $cargo_wagon Cargo wagon
 * @property $weapon_wagon Weapon wagon
 */
class Wagon extends Model
{
    protected $fillable = [
        'train_id',
        'name',
        'type',
        'weight',
        'armor',
        'max_armor',
        'lvl',
        'price',
        'upgrade_cost',
    ];

    //Relations

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    public function cargo_wagon(): HasOne
    {
        return $this->hasOne(CargoWagon::class);
    }

    public function weapon_wagon(): HasOne
    {
        return $this->hasOne(WeaponWagon::class);
    }
}
