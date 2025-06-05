<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $player_id Player ID
 * @property $locomotive Locomotive
 * @property $wagons Wagons
 */
class Train extends Model
{

    protected $fillable = [
        'player_id'
    ];

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
