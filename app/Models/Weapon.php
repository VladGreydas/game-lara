<?php

namespace App\Models;

use App\Traits\Repairable;
use App\Traits\Upgradeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property $weapon_wagon_id Weapon wagon ID
 * @property $name Weapon name
 * @property $damage Weapon damage
 * @property $price Weapon price
 * @property $lvl Weapon level
 * @property $type Weapon type
 * @property $upgrade_cost Weapon upgrade cost
 * @property WeaponWagon $weapon_wagon Weapon Wagon
 */
class Weapon extends Model
{
    use HasFactory, Repairable, Upgradeable;

    protected $fillable = [
        'weapon_wagon_id',
        'name',
        'damage',
        'type',
        'price',
        'lvl',
        'upgrade_cost'
    ];

    public static function types(): array
    {
        return [
            'HMG' => 'Heavy Machine Gun',
            'Cannon' => 'Cannon',
            'Mortar' => 'Mortar',
            'Laser' => 'Laser',
            'Rocket' => 'Rocket Launcher',
            'Flamethrower' => 'Flamethrower',
        ];
    }

    public function lvlUp(): bool
    {
        $player = $this->weapon_wagon->wagon->train->player;

        if (!$player || auth()->id() !== optional($player->user)->id) {
            throw new \Exception('Unauthorized');
        }

        $scaling = match ($this->type) {
            'HMG' => ['damage' => 10],
            'Cannon' => ['damage' => 25],
            'Mortar' => ['damage' => 40],
            'Laser' => ['damage' => 20],
            'Rocket' => ['damage' => 35],
            'Flamethrower' => ['damage' => 15],
            default => ['damage' => 10],
        };

        return $this->lvlUpWith(fn () => $player, $scaling);
    }

    public function weapon_wagon(): BelongsTo
    {
        return $this->belongsTo(WeaponWagon::class);
    }
}
