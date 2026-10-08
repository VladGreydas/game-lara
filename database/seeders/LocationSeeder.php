<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityRoute;
use App\Models\Location;
use App\Models\Resource;
use App\Models\LocationResource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locationsConfig = \Config::get('locations');

        $cities = City::all();

        foreach ($cities as $city) {
            $resource = $city->resources->where('is_surplus', true)->first();

            // First: create location
            foreach ($locationsConfig as $locationConfig) {
                if ($locationConfig['produces'] == $resource->resource->slug) {
                    $location = Location::firstOrCreate(
                        ['slug' => Str::slug($city->name . ' ' . $locationConfig['name'])],
                        [
                            'name' => $city->name . ' ' . $locationConfig['name'],
                            'city_id' => $city->id,
                            'type' => $locationConfig['type'],
                        ]
                    );
                    break;
                }
            }

            if (isset($location)) {
                // Second: create location resource for this location
                LocationResource::firstOrCreate(
                    [
                        'location_id' => $location->id,
                        'resource_id' => $resource->resource->id,
                    ],
                    [
                        'production_rate_per_hour' => 10.00,
                        'max_capacity' => 1000,
                        'current_amount' => 0,
                    ]
                );

                // Third: create routes between city and location
                CityRoute::firstOrCreate(
                    [
                        'from_city_id' => $city->id,
                        'to_location_id' => $location->id,
                        'type' => 'city_to_location',
                        'distance_km' => 50
                    ]
                );
                CityRoute::firstOrCreate(
                    [
                        'from_location_id' => $location->id,
                        'to_city_id' => $city->id,
                        'type' => 'location_to_city',
                        'distance_km' => 50
                    ]
                );
            }
            unset($location);
        }
    }
}
