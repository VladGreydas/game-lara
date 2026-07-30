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
            ['from' => 1, 'to' => 2,  'distance_km' => 200, 'bidirectional' => true], // Ironforge <-> Silverbrook
            ['from' => 3, 'to' => 4,  'distance_km' => 300, 'bidirectional' => true], // Ashenvale <-> Rivermoor
            ['from' => 5, 'to' => 6,  'distance_km' => 400, 'bidirectional' => true], // Stormhelm <-> Frostgate
            ['from' => 8, 'to' => 9,  'distance_km' => 100, 'bidirectional' => true], // Ebonreach <-> Thornhall

            // Односторонні маршрути
            ['from' => 2, 'to' => 3,  'distance_km' => 100], // Silverbrook -> Ashenvale
            ['from' => 4, 'to' => 5,  'distance_km' => 200], // Rivermoor -> Stormhelm
            ['from' => 6, 'to' => 7,  'distance_km' => 300], // Frostgate -> Dreadmoor
            ['from' => 7, 'to' => 8,  'distance_km' => 100], // Dreadmoor -> Ebonreach
            ['from' => 10, 'to' => 1, 'distance_km' => 400], // Sunspire -> Ironforge
            ['from' => 9, 'to' => 10, 'distance_km' => 200], // Thornhall -> Sunspire
        ];

        foreach ($routes as $routeData) {
            $fromId = $routeData['from'];
            $toId = $routeData['to'];
            $distanceKm = $routeData['distance_km'];

            // Create route from A to B
            CityRoute::firstOrCreate(
                [
                    'from_id' => $fromId,
                    'to_id' => $toId,
                    'type' => 'city_to_city'
                ],
                [
                    'distance_km' => $distanceKm,
                ]
            );

            // If bidirectional, create route from B to A
            if (isset($routeData['bidirectional']) && $routeData['bidirectional']) {
                CityRoute::firstOrCreate(
                    [
                        'from_id' => $toId,
                        'to_id' => $fromId,
                        'type' => 'city_to_city'
                    ],
                    [
                        'distance_km' => $distanceKm,
                    ]
                );
            }
        }
    }
}
