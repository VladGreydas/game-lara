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
            ...self::makePassenger($count),
            ...self::makeExplorer($count),
            ...self::makeScrapper($count),
            ...self::makeCrafter($count),
        ]);
    }

    public static function makeCargo(int $count = 3): array
    {
        $wagons = [];

        for ($i = 0; $i < $count; $i++) {
            $wagon = Wagon::make([
                'type' => 'cargo',
                'name' => 'Cargo Wagon MK'.$i+1,
                'weight' => 30 + $i * 10,
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
                'weight' => 35 + $i * 10,
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

    public static function makePassenger(int $count = 3): array
    {
        $wagons = [];

        for ($i = 0; $i < $count; $i++) {
            $wagon = Wagon::make([
                'type' => 'passenger',
                'name' => 'Passenger Wagon MK'.$i+1,
                'weight' => 10 + $i * 5,
                'armor' => 50 + $i * 10,
                'max_armor' => 50 + $i * 10,
                'price' => 300 + $i * 150,
                'upgrade_cost' => 150 + $i * 50,
                'lvl' => 1
            ]);
            $wagons[] = $wagon;
        }

        return $wagons;
    }

    public static function makeExplorer(int $count = 3): array
    {
        $wagons = [];

        for ($i = 0; $i < $count; $i++) {
            $wagon = Wagon::make([
                'type' => 'explorer',
                'name' => 'Explorer Wagon MK'.$i+1,
                'weight' => 12 + $i * 5,
                'armor' => 75 + $i * 10,
                'max_armor' => 75 + $i * 10,
                'price' => 400 + $i * 200,
                'upgrade_cost' => 200 + $i * 75,
                'lvl' => 1
            ]);
            $wagons[] = $wagon;
        }

        return $wagons;
    }

    public static function makeScrapper(int $count = 3): array
    {
        $wagons = [];

        for ($i = 0; $i < $count; $i++) {
            $wagon = Wagon::make([
                'type' => 'scrapper',
                'name' => 'Scrapper Wagon MK'.$i+1,
                'weight' => 20 + $i * 5,
                'armor' => 150 + $i * 20,
                'max_armor' => 150 + $i * 20,
                'price' => 600 + $i * 300,
                'upgrade_cost' => 300 + $i * 100,
                'lvl' => 1
            ]);
            $wagons[] = $wagon;
        }

        return $wagons;
    }

    public static function makeCrafter(int $count = 3): array
    {
        $wagons = [];

        for ($i = 0; $i < $count; $i++) {
            $wagon = Wagon::make([
                'type' => 'crafter',
                'name' => 'Crafter Wagon MK'.$i+1,
                'weight' => 15 + $i * 5,
                'armor' => 100 + $i * 15,
                'max_armor' => 100 + $i * 15,
                'price' => 500 + $i * 250,
                'upgrade_cost' => 250 + $i * 100,
                'lvl' => 1
            ]);
            // дані для збереження CrafterWagon
            $craftSlots = 3 + $i * 1;
            $wagon->crafter_data = ['craft_slots' => $craftSlots];
            $wagons[] = $wagon;
        }

        return $wagons;
    }
}

