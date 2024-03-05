<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Weapon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'damage',
        'type',
        'price',
        'lvl',
        'upgrade_cost'
    ];

    public static function getFirstWeaponData(): array
    {
        return [
            'name' => 'Milly',
            'price' => 250,
            'damage' => 100,
            'type' => 'LMG'
        ];
    }

    public function lvlUp(): bool
    {
        $player = $this->weapon_wagon->wagon->train->player;

        if($player->money >= $this->upgrade_cost) {
            $newLvl = $this->lvl + 1;
            $player->update(['money' => $player->money - $this->upgrade_cost]);
            $this->update([
                'lvl' => $newLvl,
                'damage' => $this->damage + 50 * $newLvl,
                'price' => $this->price + $this->upgrade_cost,
                'upgrade_cost' => $this->upgrade_cost + 100 * $newLvl,
            ]);
            return true;
        } else {
            return false;
        }
    }

    public function weapon_wagon(): BelongsTo
    {
        return $this->belongsTo(WeaponWagon::class);
    }
}
