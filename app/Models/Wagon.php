<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wagon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'train_id',
        'weight',
        'armor',
        'max_armor',
        'price',
        'lvl',
        'upgrade_cost'
    ];

    public function destroyRelatives()
    {
        $wagonable = $this->wagonable();
        $wagonable->delete();
    }

    public function lvlUp()
    {
        $wagonable = $this->wagonable;
        $player = $this->train->player;

        if($player->money >= $this->upgrade_cost) {
            $newLvl = $this->lvl + 1;
            $player->update(['money' => $player->money - $this->upgrade_cost]);
            if ($wagonable instanceof CargoWagon) {
                $this->update([
                    'lvl' => $newLvl,
                    'weight' => $this->weight + 50 * $newLvl,
                    'armor' => $this->max_armor + 100 * $newLvl,
                    'max_armor' => $this->max_armor + 100 * $newLvl,
                    'upgrade_cost' => $this->upgrade_cost + 100 * $newLvl
                ]);
                $newCapacity = $wagonable->capacity + 5 * $newLvl;
                $wagonable->update(['capacity' => $newCapacity]);
            } elseif ($wagonable instanceof WeaponWagon) {
                $this->update([
                    'lvl' => $newLvl,
                    'weight' => $this->weight + 100 * $newLvl,
                    'armor' => $this->max_armor + 150 * $newLvl,
                    'max_armor' => $this->max_armor + 150 * $newLvl,
                    'upgrade_cost' => $this->upgrade_cost + 100 * $newLvl
                ]);
            }
            return true;
        } else {
            return false;
        }
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    public function wagonable(): MorphTo
    {
        return $this->morphTo();
    }
}
