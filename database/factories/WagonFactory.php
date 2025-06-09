<?php

namespace Database\Factories;

use App\Models\Wagon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wagon>
 */
class WagonFactory extends Factory
{
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

    public function makeMultipleShopWagons()
    {
        $wagons = collect();

        // Cargo Wagons
        $cargoData = $this->generateShopWagons(3, 'cargo');
        foreach ($cargoData as $data) {
            $wagons->push([
                'wagon' => Wagon::factory()->make($data),
                'extra' => [
                    'capacity' => $data['capacity'],
                ],
            ]);
        }

        // Weapon Wagons
        $weaponData = $this->generateShopWagons(3, 'weapon');
        foreach ($weaponData as $data) {
            $wagons->push([
                'wagon' => Wagon::factory()->make($data),
                'extra' => [
                    'slots_available' => $data['slots_available'],
                ],
            ]);
        }

        return $wagons;
    }

    public function generateShopWagons($count, $type = 'cargo')
    {
        $faker = $this->faker;
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $lvl = 1;
            $basePrice = 800 + $i * 300;

            $data = [
                'type' => $type,
                'lvl' => $lvl,
                'armor' => 50 + $i * 10,
                'max_armor' => 50 + $i * 10,
                'weight' => 300 + $i * 100,
                'price' => $basePrice,
                'upgrade_cost' => 100 + $i * 50,
            ];

            if ($type === 'cargo') {
                $data['armor'] = 50 + $i * 10;
                $data['max_armor'] = 50 + $i * 10;
                $data['weight'] = 300 + $i * 100;
                $data['capacity'] = 10 + $i * 5;
            } elseif ($type === 'weapon') {
                $data['armor'] = 75 + $i * 10;
                $data['max_armor'] = 75 + $i * 10;
                $data['weight'] = 350 + $i * 100;
                $data['slots_available'] = 2;
            }

            $result[] = $data;
        }

        return $result;
    }
}
