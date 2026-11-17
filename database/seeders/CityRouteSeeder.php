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
            5 => 'Nagihara',
            6 => 'Frostgate',
            7 => 'Dreadmoor',
            8 => 'Ebonreach',
            9 => 'Thornhall',
            10 => 'Sunspire',
            11 => 'Goldmere',
            12 => 'Blackwood',
            13 => 'Highspire',
            14 => 'Mistvale',
            15 => 'Copperfield',
        ];

        $routes = [
            // Двосторонні маршрути
            // ['from' => ID міста 1, 'to' => ID міста 2, 'fuel_cost' => вартість пального, 'travel_time' => час подорожі (год), 'bidirectional' => чи двосторонній]
            ['from' => 1, 'to' => 2,  'distance_km' => 248, 'bidirectional' => true], // Ironforge <-> Silverbrook
            ['from' => 3, 'to' => 4,  'distance_km' => 252, 'bidirectional' => true], // Ashenvale <-> Rivermoor
            ['from' => 5, 'to' => 6,  'distance_km' => 245, 'bidirectional' => true], // Nagihara <-> Frostgate
            ['from' => 8, 'to' => 9,  'distance_km' => 255, 'bidirectional' => true], // Ebonreach <-> Thornhall
            ['from' => 1, 'to' => 7,  'distance_km' => 354, 'bidirectional' => true],  // Ironforge <-> Dreadmoor
            ['from' => 3, 'to' => 6,  'distance_km' => 238, 'bidirectional' => true],  // Ashenvale <-> Frostgate
            ['from' => 2, 'to' => 9,  'distance_km' => 412, 'bidirectional' => true],  // Silverbrook <-> Thornhall

            // Односторонні маршрути
            ['from' => 2, 'to' => 3,  'distance_km' => 248], // Silverbrook -> Ashenvale
            ['from' => 4, 'to' => 5,  'distance_km' => 238], // Rivermoor -> Nagihara
            ['from' => 6, 'to' => 7,  'distance_km' => 252], // Frostgate -> Dreadmoor
            ['from' => 7, 'to' => 8,  'distance_km' => 184], // Dreadmoor -> Ebonreach
            ['from' => 10, 'to' => 1, 'distance_km' => 785], // Sunspire -> Ironforge
            ['from' => 9, 'to' => 10, 'distance_km' => 248], // Thornhall -> Sunspire
            ['from' => 5, 'to' => 10, 'distance_km' => 185], // Nagihara -> Sunspire
            ['from' => 8, 'to' => 2,  'distance_km' => 365], // Ebonreach -> Silverbrook

            // Нові маршрути для 5 додаткових міст
            ['from' => 1, 'to' => 11,  'distance_km' => 210, 'bidirectional' => true], // Ironforge <-> Goldmere
            ['from' => 11, 'to' => 12, 'distance_km' => 175, 'bidirectional' => true], // Goldmere <-> Blackwood
            ['from' => 4, 'to' => 13,  'distance_km' => 265, 'bidirectional' => true], // Rivermoor <-> Highspire
            ['from' => 13, 'to' => 14, 'distance_km' => 198, 'bidirectional' => true], // Highspire <-> Mistvale
            ['from' => 9, 'to' => 15,  'distance_km' => 230, 'bidirectional' => true], // Thornhall <-> Copperfield
            ['from' => 12, 'to' => 15, 'distance_km' => 310], // Blackwood -> Copperfield
            ['from' => 14, 'to' => 6,  'distance_km' => 220], // Mistvale -> Frostgate
        ];

        foreach ($routes as $routeData) {
            $fromId = $routeData['from'];
            $toId = $routeData['to'];
            $distanceKm = $routeData['distance_km'];

            // Create route from A to B
            CityRoute::firstOrCreate(
                [
                    'from_city_id' => $fromId,
                    'to_city_id' => $toId,
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
                        'from_city_id' => $toId,
                        'to_city_id' => $fromId,
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
