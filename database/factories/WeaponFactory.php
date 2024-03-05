<?php

namespace Database\Factories;

use App\Models\Weapon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Weapon>
 */
class WeaponFactory extends Factory
{
    protected $model = Weapon::class;

    private $adjectives = [
        'Heavy',
        'Deadly',
        'Lethal',
        'Powerful',
        'Destructive',
        'Precise',
        'Reliable',
        'Versatile',
        'Tactical',
        'Agile',
        'Devastating'
    ];

    private $weapon_prices = array(
        "LMG" => 250,
        "HMG" => 750,
        "Mortar" => 1000,
        "Light Cannon" => 1250,
        "Heavy Cannon" => 1500,
        "Rocket Launcher" => 2000
    );

    private $weapon_damage = array(
        "LMG" => 100,
        "HMG" => 250,
        "Mortar" => 300,
        "Light Cannon" => 300,
        "Heavy Cannon" => 450,
        "Rocket Launcher" => 400
    );

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }

    public function makeMultipleShopWeapons()
    {
        $weapons = collect();

        //LMG
        $weapon_data = $this->generateWeapons(3, 'LMG');
        foreach ($weapon_data as $data) {
            $weapons->push(Weapon::factory()->make($data));
        }

        //HMG
        $weapon_data = $this->generateWeapons(3, 'HMG');
        foreach ($weapon_data as $data) {
            $weapons->push(Weapon::factory()->make($data));
        }

        //Mortar
        $weapon_data = $this->generateWeapons(3, 'Mortar');
        foreach ($weapon_data as $data) {
            $weapons->push(Weapon::factory()->make($data));
        }

        //Light Cannon
        $weapon_data = $this->generateWeapons(3, 'Light Cannon');
        foreach ($weapon_data as $data) {
            $weapons->push(Weapon::factory()->make($data));
        }

        //Heavy Cannon
        $weapon_data = $this->generateWeapons(3, 'Heavy Cannon');
        foreach ($weapon_data as $data) {
            $weapons->push(Weapon::factory()->make($data));
        }

        //Rocket Launcher
        $weapon_data = $this->generateWeapons(3, 'Rocket Launcher');
        foreach ($weapon_data as $data) {
            $weapons->push(Weapon::factory()->make($data));
        }

        return $weapons;
    }

    public function generateWeapons($count, $type)
    {
        $startArray = [
            'name' => $this->adjectives[array_rand($this->adjectives)].' '.$this->faker->firstName,
            'type' => $type,
            'damage' => $this->weapon_damage[$type],
            'price' => $this->weapon_prices[$type],
            'upgrade_cost' => 100,
        ];
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $startArray;
            $startArray['name'] = $this->adjectives[array_rand($this->adjectives)].' '.$this->faker->firstName;
            $startArray['damage'] += 250;
            $startArray['price'] += 500 * ($i + 1);
        }
        return $result;
    }
}
