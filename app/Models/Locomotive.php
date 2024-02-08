<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Locomotive extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'weight',
        'type',
        'power',
        'armor',
        'max_armor',
        'fuel',
        'max_fuel',
        'price',
        'lvl',
        'upgrade_cost'
    ];

    public function getWagonCap(): int
    {
        return floor($this->power / 100);
    }

    public function lvlUp()
    {
        $player = $this->train->player;

        if($player->money >= $this->upgrade_cost) {
            $newLvl = $this->lvl + 1;
            $player->update(['money' => $player->money - $this->upgrade_cost]);
            $this->update([
                'lvl' => $newLvl,
                'weight' => $this->weight + 150 * $newLvl,
                'power' => $this->power + 50 * $newLvl,
                'armor' => $this->max_armor + 100 * $newLvl,
                'max_armor' => $this->max_armor + 100 * $newLvl,
                'fuel' => $this->max_fuel + 5 * $newLvl,
                'max_fuel' => $this->max_fuel + 5 * $newLvl,
                'upgrade_cost' => $this->upgrade_cost + 100 * $newLvl
            ]);
            return true;
        } else {
            return false;
        }
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }
}
