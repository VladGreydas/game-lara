<?php

namespace Tests\Feature;

use App\Models\Resource;
use Database\Seeders\ResourceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceTierTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_seeder_seeds_t0_and_t1_resources(): void
    {
        $this->seed(ResourceSeeder::class);

        $t0Slugs = [
            'wood',
            'iron-ore',
            'coal',
            'grain',
            'copper-ore',
            'stone',
            'crude-oil',
            'clay',
            'sand',
        ];

        $t1Slugs = [
            'planks',
            'iron-ingot',
            'coke',
            'flour',
            'copper-ingot',
            'stone-bricks',
            'refined-oil',
            'bricks',
            'glass',
        ];

        foreach ($t0Slugs as $slug) {
            $resource = Resource::where('slug', $slug)->first();
            $this->assertNotNull($resource, "T0 resource with slug '{$slug}' should exist.");
            $this->assertEquals('0', (string) $resource->tier);
        }

        foreach ($t1Slugs as $slug) {
            $resource = Resource::where('slug', $slug)->first();
            $this->assertNotNull($resource, "T1 resource with slug '{$slug}' should exist.");
            $this->assertEquals('1', (string) $resource->tier);
        }
    }

    public function test_all_configured_resources_have_translations(): void
    {
        $this->seed(ResourceSeeder::class);
        $resources = Resource::all();

        foreach ($resources as $resource) {
            $enTrans = __('resource.' . $resource->name, [], 'en');
            $ukTrans = __('resource.' . $resource->name, [], 'uk');

            $this->assertNotEmpty($enTrans);
            $this->assertNotEmpty($ukTrans);
            $this->assertNotEquals('resource.' . $resource->name, $enTrans);
            $this->assertNotEquals('resource.' . $resource->name, $ukTrans);
        }
    }

    public function test_specific_t1_resource_properties(): void
    {
        $this->seed(ResourceSeeder::class);

        $ironIngot = Resource::where('slug', 'iron-ingot')->first();
        $this->assertNotNull($ironIngot);
        $this->assertEquals('Iron Ingot', $ironIngot->name);
        $this->assertEquals('1', (string) $ironIngot->tier);

        $coke = Resource::where('slug', 'coke')->first();
        $this->assertNotNull($coke);
        $this->assertEquals('Coke', $coke->name);
        $this->assertEquals('1', (string) $coke->tier);
        $this->assertTrue((bool) $coke->is_fuel);
        $this->assertEquals(2, $coke->fuel_value);

        $refinedOil = Resource::where('slug', 'refined-oil')->first();
        $this->assertNotNull($refinedOil);
        $this->assertTrue((bool) $refinedOil->is_fuel);
    }
}
