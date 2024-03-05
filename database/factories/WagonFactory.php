<?php

namespace Database\Factories;

use App\Models\CargoWagon;
use App\Models\Wagon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wagon>
 */
class WagonFactory extends Factory
{
    protected $model = Wagon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [];
    }

    public function makeMultipleShopCargoWagons()
    {
        return $this->generateShopWagons(3, 'Cargo');
    }

    public function makeMultipleShopWeaponWagons()
    {
        return $this->generateShopWagons(3, 'Weapon');
    }

    public function generateShopWagons($count, $type = 'Cargo'): array
    {
        $startArray = [
            'name' => 'Cargo Wagon 1',
            'weight' => 125,
            'armor' => 500,
            'max_armor' => 500,
            'price' => 250,
            'lvl' => 1,
            'upgrade_cost' => 100,
            'type' => $type
        ];
        $result = [];
        switch ($type) {
            case 'Cargo': {
                $startArray['capacity'] = 10;

                for ($i = 0; $i < $count; $i++) {
                    $startArray['id'] = $i+1;
                    $result[] = $startArray;
                    $startArray['name'] = substr($startArray['name'], 0, -1).$i+2;
                    $startArray['weight'] += 500;
                    $startArray['armor'] += 750;
                    $startArray['max_armor'] += 500;
                    $startArray['price'] += 500 * ($i + 1);
                    $startArray['capacity'] += 25;
                }

                break;
            }
            case 'Weapon': {
                $startArray['name'] = 'Weapon Wagon 1';
                $startArray['weight'] = 150;
                $startArray['armor'] = 600;
                $startArray['max_armor'] = 600;
                $startArray['price'] = 300;
                $startArray['slots_available'] = 2;

                for ($i = 0; $i < $count; $i++) {
                    $startArray['id'] = $i+1;
                    $result[] = $startArray;
                    $startArray['name'] = substr($startArray['name'], 0, -1).$i+2;
                    $startArray['weight'] += 500;
                    $startArray['armor'] += 750;
                    $startArray['max_armor'] += 750;
                    $startArray['price'] += 500 * ($i + 1);
                }
                break;
            }
            default: break;
        }
        return $result;
    }
}
