<?php

namespace Database\Factories;

use App\Models\Locomotive;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Locomotive>
 */
class LocomotiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
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
        ];
    }

    public function makeMultipleShopLocomotives()
    {
        $locomotives = collect();

        //Steam
        $locomotiveData = $this->generateShopLocomotives(3);

        foreach ($locomotiveData as $data) {
            $locomotives->push(Locomotive::factory()->make($data));
        }

        //Diesel
        $locomotiveData = $this->generateShopLocomotives(3, 'Diesel');

        foreach ($locomotiveData as $data) {
            $locomotives->push(Locomotive::factory()->make($data));
        }

        return $locomotives;
    }

    public function generateShopLocomotives($count, $type = 'Steam')
    {
        $startArray = [
            'name' => 'Elrick',
            'weight' => 400,
            'type' => 'Steam',
            'power' => 3750,
            'armor' => 65,
            'max_armor' => 65,
            'fuel' => 25,
            'max_fuel' => 25,
            'price' => 1250,
            'lvl' => 1,
            'upgrade_cost' => 100
        ];

        switch ($type) {
            case 'Diesel': {
                $startArray['name'] = 'Mary';
                $startArray['type'] = 'Diesel';
                $startArray['weight'] = 600;
                $startArray['power'] = 6000;
                $startArray['armor'] = 100;
                $startArray['max_armor'] = 100;
                $startArray['fuel'] = 50;
                $startArray['max_fuel'] = 50;
                $startArray['price'] = 2000;
                break;
            }
            default: break;
        }
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $startArray;
            $startArray['name'] = $this->faker->firstName;
            $startArray['weight'] += 250;
            $startArray['power'] += 2500;
            $startArray['armor'] += 50;
            $startArray['max_armor'] += 50;
            $startArray['fuel'] += 25;
            $startArray['max_fuel'] += 25;
            $startArray['price'] += 500 * ($i + 1);
        }
        return $result;
    }
}
