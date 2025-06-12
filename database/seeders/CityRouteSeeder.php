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
            // Двосторонні маршрути
            // ['from' => ID міста 1, 'to' => ID міста 2, 'fuel_cost' => вартість пального, 'travel_time' => час подорожі (год), 'bidirectional' => чи двосторонній]
            ['from' => 1, 'to' => 2,  'fuel_cost' => 10, 'bidirectional' => true], // Ironforge <-> Silverbrook
            ['from' => 3, 'to' => 4,  'fuel_cost' => 15, 'bidirectional' => true], // Ashenvale <-> Rivermoor
            ['from' => 5, 'to' => 6,  'fuel_cost' => 20, 'bidirectional' => true], // Stormhelm <-> Frostgate
            ['from' => 8, 'to' => 9,  'fuel_cost' => 5,  'bidirectional' => true], // Ebonreach <-> Thornhall

            // Односторонні маршрути
            ['from' => 2, 'to' => 3,  'fuel_cost' => 5],  // Silverbrook -> Ashenvale
            ['from' => 4, 'to' => 5,  'fuel_cost' => 10], // Rivermoor -> Stormhelm
            ['from' => 6, 'to' => 7,  'fuel_cost' => 15], // Frostgate -> Dreadmoor
            ['from' => 7, 'to' => 8,  'fuel_cost' => 5],  // Dreadmoor -> Ebonreach
            ['from' => 10, 'to' => 1, 'fuel_cost' => 20], // Sunspire -> Ironforge
            ['from' => 9, 'to' => 10, 'fuel_cost' => 10], // Thornhall -> Sunspire
        ];

        foreach ($routes as $routeData) {
            $fromCityId = $routeData['from'];
            $toCityId = $routeData['to'];
            $fuelCost = $routeData['fuel_cost'];

            // Calculate travel_time based on fuel_cost, minimum 1 hour
            $travelTime = max(1, (int) ceil($fuelCost / 5)); // Changed this line

            // Create route from A to B
            CityRoute::firstOrCreate(
                [
                    'from_city_id' => $fromCityId,
                    'to_city_id' => $toCityId,
                ],
                [
                    'fuel_cost' => $fuelCost,
                    'travel_time' => $travelTime, // Added travel_time here
                ]
            );

            // If bidirectional, create route from B to A
            if (isset($routeData['bidirectional']) && $routeData['bidirectional']) {
                CityRoute::firstOrCreate(
                    [
                        'from_city_id' => $toCityId,
                        'to_city_id' => $fromCityId,
                    ],
                    [
                        'fuel_cost' => $fuelCost,
                        'travel_time' => $travelTime, // Added travel_time here
                    ]
                );
            }
        }
    }
}
