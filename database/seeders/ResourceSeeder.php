<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Resource; // Не забудьте імпортувати модель Resource

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            [
                'name' => 'Wood',
                'slug' => 'wood',
                'description' => 'A basic construction material, widely available.',
                'unit' => 'unit',
                'base_price' => 10,
            ],
            [
                'name' => 'Iron Ore',
                'slug' => 'iron-ore',
                'description' => 'Raw material for metal production.',
                'unit' => 'unit',
                'base_price' => 15,
            ],
            [
                'name' => 'Coal',
                'slug' => 'coal',
                'description' => 'Essential fuel for locomotives and industrial processes.',
                'unit' => 'unit',
                'is_fuel' => '1',
                'fuel_value' => '1',
                'base_price' => 15,
            ],
            [
                'name' => 'Grain',
                'slug' => 'grain',
                'description' => 'Staple food, can be processed into other goods.',
                'unit' => 'unit',
                'base_price' => 5,
            ],
            [
                'name' => 'Water',
                'slug' => 'water',
                'description' => 'Necessary for many processes and for population needs.',
                'unit' => 'liter',
                'base_price' => 5,
            ],
        ];

        foreach ($resources as $resourceData) {
            Resource::firstOrCreate(
                ['slug' => $resourceData['slug']], // Шукаємо за slug, щоб уникнути дублікатів
                $resourceData
            );
        }
    }
}
