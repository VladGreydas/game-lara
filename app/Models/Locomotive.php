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
        return floor($this->power / 1000);
    }

    public function lvlUp()
    {
        $player = $this->train->player;

        if($player->money >= $this->upgrade_cost && !$this->isMaxed()) {
            $newLvl = $this->lvl + 1;
            $player->update(['money' => $player->money - $this->upgrade_cost]);
            $this->update([
                'lvl' => $newLvl,
                'weight' => $this->weight + 50 * $newLvl,
                'power' => $this->power + 500 * $newLvl,
                'armor' => $this->max_armor + 100 * $newLvl,
                'max_armor' => $this->max_armor + 100 * $newLvl,
                'fuel' => $this->max_fuel + 5 * $newLvl,
                'max_fuel' => $this->max_fuel + 5 * $newLvl,
                'price' => $this->price + $this->upgrade_cost,
                'upgrade_cost' => $this->upgrade_cost + 100 * $newLvl
            ]);
            return true;
        } else {
            return false;
        }
    }

    public function purchase(Locomotive $new_locomotive)
    {
        $player = $this->train->player;

        if ($this->price < $new_locomotive->price) {
            if($player->money >= $new_locomotive->price - $this->price) {
                $player->update(['money' => $player->money - $this->price]);
                $this->update([
                    'lvl' => 1,
                    'weight' => $new_locomotive->weight,
                    'type' => $new_locomotive->type,
                    'power' => $new_locomotive->power,
                    'armor' => $new_locomotive->max_armor,
                    'max_armor' => $new_locomotive->max_armor,
                    'fuel' => $new_locomotive->max_fuel,
                    'max_fuel' => $new_locomotive->max_fuel,
                    'price' => $new_locomotive->price,
                    'upgrade_cost' => $new_locomotive->upgrade_cost,
                    'name' => $new_locomotive->name
                ]);
                return true;
            } else {
                return false;
            }
        } elseif ($this->price > $new_locomotive->price) {
            $cashback = $player->money + ($this->price - $new_locomotive->price);
            $player->update(['money' => $cashback]);
            $this->update([
                'lvl' => 1,
                'weight' => $new_locomotive->weight,
                'type' => $new_locomotive->type,
                'power' => $new_locomotive->power,
                'armor' => $new_locomotive->max_armor,
                'max_armor' => $new_locomotive->max_armor,
                'fuel' => $new_locomotive->max_fuel,
                'max_fuel' => $new_locomotive->max_fuel,
                'price' => $new_locomotive->price,
                'upgrade_cost' => $new_locomotive->upgrade_cost,
                'name' => $new_locomotive->name
            ]);
            return true;
        } else {
            $this->update([
                'lvl' => 1,
                'weight' => $new_locomotive->weight,
                'type' => $new_locomotive->type,
                'power' => $new_locomotive->power,
                'armor' => $new_locomotive->max_armor,
                'max_armor' => $new_locomotive->max_armor,
                'fuel' => $new_locomotive->max_fuel,
                'max_fuel' => $new_locomotive->max_fuel,
                'price' => $new_locomotive->price,
                'upgrade_cost' => $new_locomotive->upgrade_cost,
                'name' => $new_locomotive->name
            ]);
            return true;
        }
    }

    public function isMaxed()
    {
        return $this->lvl >= 10;
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }
}
