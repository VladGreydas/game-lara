<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\CityResource;
use App\Models\Location;
use App\Models\LocationResource;
use App\Models\Resource;
use Database\Seeders\CitiesSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ResourceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_resources_have_a_configured_location(): void
    {
        $this->seed(ResourceSeeder::class);
        $locationsConfig = config('locations');

        $resources = Resource::all();
        $this->assertCount(18, $resources);

        $configuredSlugs = collect($locationsConfig)->pluck('produces')->toArray();

        foreach ($resources as $resource) {
            $this->assertContains(
                $resource->slug,
                $configuredSlugs,
                "Resource '{$resource->slug}' should have a corresponding location producing it in config/locations.php."
            );
        }
    }

    public function test_all_location_types_have_translations_in_english_and_ukrainian(): void
    {
        $locationsConfig = config('locations');

        foreach ($locationsConfig as $location) {
            $type = $location['type'];

            $enTranslation = __('location.types.' . $type, [], 'en');
            $ukTranslation = __('location.types.' . $type, [], 'uk');

            $this->assertNotEmpty($enTranslation);
            $this->assertNotEmpty($ukTranslation);
            $this->assertNotEquals('location.types.' . $type, $enTranslation);
            $this->assertNotEquals('location.types.' . $type, $ukTranslation);
        }
    }

    public function test_location_seeder_creates_location_for_t1_surplus_resources(): void
    {
        $this->seed(ResourceSeeder::class);

        $t1Resource = Resource::where('slug', 'iron-ingot')->firstOrFail();

        $city = City::create([
            'id' => 1,
            'name' => 'Steelton',
            'level' => 1,
            'max_level' => 10,
        ]);

        CityResource::create([
            'city_id' => $city->id,
            'resource_id' => $t1Resource->id,
            'quantity' => 1500,
            'base_quantity' => 1000,
            'buy_price' => $t1Resource->base_price,
            'sell_price' => $t1Resource->base_price / 1.5,
            'price_multiplier' => 1.0,
            'is_surplus' => true,
            'is_deficit' => false,
        ]);

        $this->seed(LocationSeeder::class);

        $location = Location::where('city_id', $city->id)->first();
        $this->assertNotNull($location, 'Location should be created for city with T1 surplus.');
        $this->assertEquals('iron_smelter', $location->type);
        $this->assertEquals('Steelton Iron Smelter', $location->name);

        $locationResource = LocationResource::where('location_id', $location->id)->first();
        $this->assertNotNull($locationResource);
        $this->assertEquals($t1Resource->id, $locationResource->resource_id);
    }

    public function test_full_database_seeding_creates_locations_without_errors(): void
    {
        $this->seed(ResourceSeeder::class);
        $this->seed(CitiesSeeder::class);
        $this->seed(LocationSeeder::class);

        $locations = Location::all();
        $this->assertNotEmpty($locations);

        foreach ($locations as $location) {
            $this->assertNotNull($location->location_resource);
            $this->assertNotNull($location->location_resource->resource);
            $this->assertNotNull($location->getOutgoingRoute());
        }
    }
}
