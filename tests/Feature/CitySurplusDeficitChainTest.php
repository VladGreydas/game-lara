<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\CityResource;
use App\Models\Resource;
use Database\Seeders\CitiesSeeder;
use Database\Seeders\ResourceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitySurplusDeficitChainTest extends TestCase
{
    use RefreshDatabase;

    public function test_tier1_surplus_resource_is_paired_with_its_tier0_deficit_resource(): void
    {
        $this->seed(ResourceSeeder::class);
        $this->seed(CitiesSeeder::class);

        $t1ToT0 = [
            'planks' => 'wood',
            'iron-ingot' => 'iron-ore',
            'coke' => 'coal',
            'flour' => 'grain',
            'copper-ingot' => 'copper-ore',
            'stone-bricks' => 'stone',
            'refined-oil' => 'crude-oil',
            'bricks' => 'clay',
            'glass' => 'sand',
        ];

        $checkedAtLeastOne = false;

        foreach (City::all() as $city) {
            $surplusCityResource = CityResource::where('city_id', $city->id)
                ->where('is_surplus', true)
                ->first();

            $this->assertNotNull($surplusCityResource, "City '{$city->name}' should have a surplus resource.");

            $surplusResource = Resource::find($surplusCityResource->resource_id);

            if ($surplusResource->tier !== '1' || empty($surplusResource->produced_from)) {
                continue;
            }

            $this->assertSame(
                $t1ToT0[$surplusResource->slug] ?? null,
                $surplusResource->produced_from
            );

            $deficitCityResource = CityResource::where('city_id', $city->id)
                ->where('is_deficit', true)
                ->first();

            $this->assertNotNull($deficitCityResource, "City '{$city->name}' should have a deficit resource.");

            $deficitResource = Resource::find($deficitCityResource->resource_id);

            $this->assertSame($surplusResource->produced_from, $deficitResource->slug);

            $checkedAtLeastOne = true;
        }

        $this->assertTrue($checkedAtLeastOne, 'Expected at least one city with a T1 surplus resource to verify the T0 deficit pairing.');
    }

    public function test_every_city_has_exactly_one_surplus_and_one_deficit_resource(): void
    {
        $this->seed(ResourceSeeder::class);
        $this->seed(CitiesSeeder::class);

        foreach (City::all() as $city) {
            $surplusCount = CityResource::where('city_id', $city->id)->where('is_surplus', true)->count();
            $deficitCount = CityResource::where('city_id', $city->id)->where('is_deficit', true)->count();

            $this->assertSame(1, $surplusCount, "City '{$city->name}' should have exactly one surplus resource.");
            $this->assertSame(1, $deficitCount, "City '{$city->name}' should have exactly one deficit resource.");
        }
    }
}
