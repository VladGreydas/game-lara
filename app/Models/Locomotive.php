<?php

namespace App\Models;

use App\Traits\Repairable;
use App\Traits\Upgradeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id Locomotive ID
 * @property int $train_id Locomotive's train ID
 * @property string $name Locomotive's name
 * @property int $weight Locomotive's weight
 * @property string $type Locomotive's type - steam, diesel, electric, etc.
 * @property int $power Locomotive's power
 * @property int $armor Locomotive's armor
 * @property int $max_armor Locomotive's max armor
 * @property int $fuel Locomotive's fuel
 * @property int $max_fuel Locomotive's max fuel
 * @property int $price Locomotive's price
 * @property int $lvl Current locomotive level
 * @property int $upgrade_cost Locomotive's upgrade cost
 * @property Train $train Locomotive's train
 */
class Locomotive extends Model
{
    use HasFactory, Repairable, Upgradeable;

    protected $fillable = [
        'train_id',
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

    public function repair(): bool
    {
        return $this->repairWith(
            fn ($locomotive) => $locomotive->train->player,
            'armor',
            'max_armor',
            3
        );
    }

    public function lvlUp(): bool
    {
        return $this->lvlUpWith(
            fn($locomotive) => $locomotive->train->player,
            [
                'weight' => 50,
                'power' => 500,
                'armor' => 100,
                'max_armor' => 100,
                'fuel' => 5,
                'max_fuel' => 5,
            ],
            10
        );
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }
}
