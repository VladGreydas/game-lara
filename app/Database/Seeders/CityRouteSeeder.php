<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityRoute;
use Illuminate\Database\Seeder;

class CityRouteSeeder extends Seeder
{
    public function run(): void
    {
        $cities = City::all();

        // Створюємо маршрути між сусідніми містами (лінійно)
        for ($i = 0; $i < $cities->count() - 1; $i++) {
            $from = $cities->get($i);
            $to = $cities->get($i + 1);

            // city_to_city
            CityRoute::firstOrCreate(
                ['from_id' => $from->id, 'to_id' => $to->id, 'type' => 'city_to_city'],
                ['fuel_cost' => 10, 'travel_time' => 1]
            );

            // Зворотній маршрут (двосторонній)
            CityRoute::firstOrCreate(
                ['from_id' => $to->id, 'to_id' => $from->id, 'type' => 'city_to_city'],
                ['fuel_cost' => 10, 'travel_time' => 1]
            );
        }
    }
}
