<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Train extends Model
{
    use HasFactory;

    public function firstCreation(): void
    {
        $this->locomotive()->create();

        $this->createFirstCargoWagon();
        $this->createFirstWeaponWagon();
    }

    public function createFirstCargoWagon(): void
    {
        $cargo_stats = CargoWagon::getFirstCargoWagonData();
        $cargo_stats['train_id'] = $this->id;
        $cargo = CargoWagon::create(['name' => $cargo_stats['name'], 'capacity' => CargoWagon::CARGO_FIRST_CAPACITY]);
        $cargo->wagon()->create($cargo_stats);
    }

    public function createFirstWeaponWagon(): void
    {
        $weapon_stats = WeaponWagon::getFirstWeaponWagonData();
        $weapon_stats['train_id'] = $this->id;
        $weapon = WeaponWagon::create();
        $weapon->wagon()->create($weapon_stats);
        $weapon = WeaponWagon::get()->last();
        $weapon->addWeapon();
    }

    /*
     * Relations
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function locomotive(): HasOne
    {
        return $this->hasOne(Locomotive::class);
    }

    public function wagon(): HasMany
    {
        return $this->hasMany(Wagon::class)->with(['wagonable']);
    }
}
