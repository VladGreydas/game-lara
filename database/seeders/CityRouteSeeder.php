<?php

namespace Database\Seeders;

use App\Models\CityRoute;
use Illuminate\Database\Seeder;

class CityRouteSeeder extends Seeder
{
    public function run(): void
    {
        // ІД міст (відповідає порядку в сидері CitySeeder)
        $cityIds = [
            1 => 'Ironforge',
            2 => 'Silverbrook',
            3 => 'Ashenvale',
            4 => 'Rivermoor',
            5 => 'Stormhelm',
            6 => 'Frostgate',
            7 => 'Dreadmoor',
            8 => 'Ebonreach',
            9 => 'Thornhall',
            10 => 'Sunspire',
        ];

        $routes = [
            // Двосторонні
            ['from' => 1, 'to' => 2,  'fuel_cost' => 10, 'bidirectional' => true], // Ironforge <-> Silverbrook
            ['from' => 3, 'to' => 4,  'fuel_cost' => 15, 'bidirectional' => true], // Ashenvale <-> Rivermoor
            ['from' => 5, 'to' => 6,  'fuel_cost' => 20, 'bidirectional' => true], // Stormhelm <-> Frostgate
            ['from' => 8, 'to' => 9,  'fuel_cost' => 5,  'bidirectional' => true], // Ebonreach <-> Thornhall

            // Односторонні
            ['from' => 2, 'to' => 3,  'fuel_cost' => 5],  // Silverbrook -> Ashenvale
            ['from' => 4, 'to' => 5,  'fuel_cost' => 10], // Rivermoor -> Stormhelm
            ['from' => 6, 'to' => 7,  'fuel_cost' => 15], // Frostgate -> Dreadmoor
            ['from' => 7, 'to' => 8,  'fuel_cost' => 5],  // Dreadmoor -> Ebonreach
            ['from' => 10, 'to' => 1, 'fuel_cost' => 20], // Sunspire -> Ironforge
            ['from' => 9, 'to' => 10, 'fuel_cost' => 10], // Thornhall -> Sunspire
        ];

        foreach ($routes as $route) {
            CityRoute::create([
                'from_city_id' => $route['from'],
                'to_city_id' => $route['to'],
                'fuel_cost' => $route['fuel_cost'],
            ]);

            if (!empty($route['bidirectional'])) {
                CityRoute::create([
                    'from_city_id' => $route['to'],
                    'to_city_id' => $route['from'],
                    'fuel_cost' => $route['fuel_cost'],
                ]);
            }
        }
    }
}
