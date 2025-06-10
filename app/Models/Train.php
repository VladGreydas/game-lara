<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $player_id Player ID
 * @property $player Player
 * @property $locomotive Locomotive
 * @property $wagons Wagons
 */
class Train extends Model
{

    protected $fillable = [
        'player_id'
    ];

    public function checkAvailableWeaponWagons()
    {
        $wagons = $this->wagons;
        $resWagons = [];
        foreach ($wagons as $wagon) {
            if ($wagon->type === 'weapon' && $wagon->weapon_wagon->slots_available > 0) {
                $resWagons[] = $wagon->weapon_wagon;
            }
        }
        return $resWagons;
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function locomotive(): HasOne
    {
        return $this->hasOne(Locomotive::class);
    }

    public function wagons(): HasMany
    {
        return $this->hasMany(Wagon::class);
    }
}
