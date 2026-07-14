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

    public function getSpeedMultiplierAttribute(): float
    {
        $locomotive = $this->locomotive;
        if (!$locomotive) return 1.0;

        $totalTrainWeight = $locomotive->weight;
        // Рахуємо загальну вагу всіх вагонів потяга
        foreach ($this->wagons as $wagon) {
            $totalTrainWeight += $wagon->getTotalWeight();
        }

        // Якщо потяг порожній, штрафу немає
        if ($totalTrainWeight == 0) return 1.0;

        // Формула співвідношення Потужності до Ваги (Power-to-Weight Ratio)
        // Наприклад: якщо Power = 100, а вага вагонів 50 тонн -> коефіцієнт 2.0 (ідеально)
        $ratio = $locomotive->power / $totalTrainWeight;

        // Якщо відношення менше 1.5 (ваги забагато для такої потужності) — потяг сповільнюється
        if ($ratio < 1.5) {
            // Отримуємо коефіцієнт уповільнення, але не дозволяємо потягу їхати повільніше ніж 30% від норми
            return max(0.3, $ratio / 1.5);
        }

        return 1.0; // Потужності вистачає, їде на повну
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
