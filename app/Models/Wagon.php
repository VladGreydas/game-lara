<?php

namespace App\Models;

use App\Traits\Upgradeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property CargoWagon $cargo_wagon Cargo wagon
 * @property WeaponWagon $weapon_wagon Weapon wagon
 * @property Train $train Train
 */
class Wagon extends Model
{
    use HasFactory, Upgradeable;
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

    public function isCargo(): bool
    {
        return $this->type === 'cargo' && $this->cargo_wagon !== null;
    }

    public function isWeapon(): bool
    {
        return $this->type === 'weapon' && $this->weapon_wagon !== null;
    }

    public function lvlUp(): bool
    {
        $player = $this->train->player;

        if (!$player || auth()->id() !== optional($player->user)->id) {
            throw new \Exception('Unauthorized');
        }

        $baseScale = [];
        $extraScale = [];

        if ($this->isCargo()) {
            $baseScale = [
                'weight' => 50,
                'armor' => 100,
                'max_armor' => 100,
            ];
            $extraScale = ['capacity' => 5];
        } elseif ($this->isWeapon()) {
            $baseScale = [
                'weight' => 100,
                'armor' => 150,
                'max_armor' => 150,
            ];
        }

        $success = $this->lvlUpWith(fn () => $player, $baseScale);

        if (!$success) {
            return false;
        }

        // Update extra fields in the related table
        if ($this->isCargo()) {
            $this->cargo_wagon->incrementEach($extraScale, $this->lvl);
        }

        return true;
    }

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
