<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class WeaponWagon extends Model
{
    use HasFactory;

    protected $fillable = [
        'slots_available'
    ];

    public static function getFirstWeaponWagonData(): array
    {
        return [
            'name' => 'Weapon Wagon',
            'weight' => 125,
            'price' => 250,
            'train_id' => 1,
            'armor' => 500,
            'max_armor' => 500
        ];
    }

    public function addWeapon(bool $first = true, array $weapon_stats = [])
    {
        if ($this->slots_available > 0) {
            if ($first) {
                $weapon_stats = Weapon::getFirstWeaponData();
                $this->weapons()->create($weapon_stats);
                $this->slots_available--;
                $this->update(['slots_available' => $this->slots_available]);
            }
        }
    }

    public function wagon(): MorphOne
    {
        return $this->morphOne(Wagon::class, 'wagonable');
    }

    public function weapons(): HasMany
    {
        return $this->hasMany(Weapon::class);
    }
}
