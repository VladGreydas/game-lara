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
        $resourcesConfig = \Config::get('resource');

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
                $locationResource = LocationResource::firstOrCreate(
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

                // If this is a T1 location (produces a processed resource), set up processing info
                $producedResource = $resource->resource;
                if ($producedResource->tier == '1' && $producedResource->produced_from) {
                    // Find the input (T0) resource
                    $inputResource = Resource::where('slug', $producedResource->produced_from)->first();

                    if ($inputResource) {
                        // Find the config entry for this resource
                        $resourceConfig = null;
                        foreach ($resourcesConfig as $rc) {
                            if ($rc['slug'] === $producedResource->slug) {
                                $resourceConfig = $rc;
                                break;
                            }
                        }

                        $updateData = [
                            'input_resource_id' => $inputResource->id,
                            'processing_ratio_input' => $resourceConfig['processing_ratio']['input'] ?? 1,
                            'processing_ratio_output' => $resourceConfig['processing_ratio']['output'] ?? 1,
                        ];

                        // If this resource has a second input (e.g., coal for smelters)
                        if (isset($resourceConfig['second_input'])) {
                            $secondInputResource = Resource::where('slug', $resourceConfig['second_input'])->first();
                            if ($secondInputResource) {
                                $updateData['input_resource_2_id'] = $secondInputResource->id;
                                $updateData['processing_ratio_input2'] = $resourceConfig['second_input_ratio'] ?? 1;
                            }
                        }

                        $locationResource->update($updateData);
                    }
                }

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
