<?php

namespace App\Factories;

use App\Models\Wagon;
use Illuminate\Support\Str;

class WagonShopFactory
{
    public static function makeShopWagons(int $count = 3): \Illuminate\Support\Collection
    {
        return collect([
            ...self::makeCargo($count),
            ...self::makeWeapon($count),
        ]);
    }

    public static function makeCargo(int $count = 3): array
    {
        $wagons = [];

        for ($i = 0; $i < $count; $i++) {
            $wagon = Wagon::make([
                'type' => 'cargo',
                'name' => 'Cargo Wagon MK'.$i+1,
                'weight' => 300 + $i * 100,
                'armor' => 50 + $i * 10,
                'max_armor' => 50 + $i * 10,
                'price' => 500 + $i * 250,
                'upgrade_cost' => 100 + $i * 50,
                'lvl' => 1
            ]);
            // дані для збереження CargoWagon
            $capacity = 10 + $i * 5;
            $wagon->cargo_data = ['capacity' => $capacity];
            $wagons[] = $wagon;
        }

        return $wagons;
    }

    public static function makeWeapon(int $count = 3): array
    {
        $wagons = [];

        for ($i = 0; $i < $count; $i++) {
            $wagon = Wagon::make([
                'type' => 'weapon',
                'name' => 'Weapon Wagon MK'.$i+1,
                'weight' => 350 + $i * 100,
                'armor' => 75 + $i * 10,
                'max_armor' => 75 + $i * 10,
                'price' => 600 + $i * 300,
                'upgrade_cost' => 150 + $i * 75,
                'lvl' => 1
            ]);
            $wagon->weapon_data = ['slots_available' => 2];
            $wagons[] = $wagon;
        }

        return $wagons;
    }
}

