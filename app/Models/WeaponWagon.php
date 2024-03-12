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
            'armor' => 500,
            'max_armor' => 500
        ];
    }

    public function addFirstWeapon()
    {
        if ($this->slots_available > 0) {
            $weapon_stats = Weapon::getFirstWeaponData();
            $this->weapons()->create($weapon_stats);
            $this->slots_available--;
            $this->update(['slots_available' => $this->slots_available]);
        }
    }

    public function purchaseWeapon($weapon)
    {
        $response = [];
        if ($this->slots_available > 0) {
            $player = $this->wagon->train->player;
            if ($player->money >= $weapon->price) {
                $this->weapons()->save($weapon);
                $player->update(['money' => ($player->money - $weapon->price)]);
                $this->slots_available--;
                $this->update(['slots_available' => $this->slots_available]);

                $response['status'] = 'success';
                $response['message'] = 'Successfully bought '.$weapon->name;
            } else {
                $response['status'] = 'failed';
                $response['message'] = 'Not enough money to buy';
            }
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Not enough available slots';
        }

        return $response;
    }

    public function addSlot()
    {
        $this->update(['slots_available' => $this->slots_available + 1]);
    }

    public function getAllWeapons()
    {
        $weapons = collect();

        foreach ($this->weapons as $weapon) {
            $weapons->push($weapon);
        }

        return $weapons;
    }

    public function isExtendable()
    {
        return $this->slots_available > 0;
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
