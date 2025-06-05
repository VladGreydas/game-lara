<?php

namespace App\Services;

use App\Models\{City, Player, Train, Locomotive, Wagon, CargoWagon, WeaponWagon, Weapon};

class PlayerSetupService
{
    public function setupInitialAssets(Player $player): void
    {
        $train = Train::create([
            'player_id' => $player->id,
        ]);

        Locomotive::create([
            'train_id' => $train->id,
            'name' => 'Starter Engine',
            'weight' => 350,
            'type' => 'steam',
            'power' => 2000,
            'armor' => 50,
            'max_armor' => 50,
            'fuel' => 10,
            'max_fuel' => 10,
            'price' => 500,
            'lvl' => 1,
            'upgrade_cost' => 100,
        ]);

        $cargoWagon = Wagon::create([
            'train_id' => $train->id,
            'name' => 'Starter Cargo Wagon',
            'type' => 'cargo',
            'weight' => 125,
            'armor' => 50,
            'max_armor' => 50,
            'lvl' => 1,
            'price' => 250,
            'upgrade_cost' => 150,
        ]);

        CargoWagon::create([
            'wagon_id' => $cargoWagon->id,
            'capacity' => 10,
        ]);

        $weaponWagon = Wagon::create([
            'train_id' => $train->id,
            'name' => 'Starter Weapon Wagon',
            'type' => 'weapon',
            'weight' => 120,
            'armor' => 70,
            'max_armor' => 70,
            'lvl' => 1,
            'price' => 700,
            'upgrade_cost' => 200,
        ]);

        $weaponWagonDetails = WeaponWagon::create([
            'wagon_id' => $weaponWagon->id,
            'slots_available' => 1,
        ]);

        Weapon::create([
            'weapon_wagon_id' => $weaponWagonDetails->id,
            'name' => 'Milly',
            'damage' => 100,
            'type' => 'LMG',
            'price' => 250,
            'lvl' => 1,
            'upgrade_cost' => 100
        ]);
    }
}
