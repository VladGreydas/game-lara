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
            ],
            [
                'name' => 'Iron Ore',
                'slug' => 'iron-ore',
                'description' => 'Raw material for metal production.',
                'unit' => 'unit',
            ],
            [
                'name' => 'Coal',
                'slug' => 'coal',
                'description' => 'Essential fuel for locomotives and industrial processes.',
                'unit' => 'unit',
            ],
            [
                'name' => 'Grain',
                'slug' => 'grain',
                'description' => 'Staple food, can be processed into other goods.',
                'unit' => 'unit',
            ],
            [
                'name' => 'Water',
                'slug' => 'water',
                'description' => 'Necessary for many processes and for population needs.',
                'unit' => 'liter',
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
