<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\CityResource;
use App\Models\CityRoute;
use App\Models\Location;
use App\Models\LocationResource;
use App\Models\Player;
use App\Models\Resource;
use App\Models\User;
use Database\Seeders\CitiesSeeder;
use Database\Seeders\CityRouteSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ResourceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_map_page(): void
    {
        $this->seed(ResourceSeeder::class);
        $this->seed(CitiesSeeder::class);
        $this->seed(LocationSeeder::class);
        $this->seed(CityRouteSeeder::class);

        $user = User::factory()->create();
        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'Engineer',
            'city_id' => City::first()->id,
            'money' => 1000,
            'lvl' => 1,
            'exp' => 0,
            'max_exp' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('map.index'));

        $response->assertOk();
        $response->assertViewIs('map.index');
        $response->assertSee('World Map');
        $response->assertSee(City::first()->name);
    }

    public function test_map_page_displays_city_surplus_resources_and_locations(): void
    {
        $this->seed(ResourceSeeder::class);

        $city = City::create([
            'name' => 'Metroville',
            'level' => 2,
            'max_level' => 10,
            'has_workshop' => true,
            'has_shop' => true,
            'has_saloon' => false,
        ]);

        $resource = Resource::where('slug', 'iron-ore')->firstOrFail();

        CityResource::create([
            'city_id' => $city->id,
            'resource_id' => $resource->id,
            'quantity' => 1800,
            'base_quantity' => 1000,
            'buy_price' => $resource->base_price,
            'sell_price' => $resource->base_price / 1.5,
            'price_multiplier' => 0.6,
            'is_surplus' => true,
            'is_deficit' => false,
        ]);

        $location = Location::create([
            'name' => 'Metroville Iron Mine',
            'slug' => 'metroville-iron-mine',
            'city_id' => $city->id,
            'type' => 'iron_mine',
        ]);

        LocationResource::create([
            'location_id' => $location->id,
            'resource_id' => $resource->id,
            'production_rate_per_hour' => 10.00,
            'max_capacity' => 1000,
            'current_amount' => 250,
        ]);

        $user = User::factory()->create();
        Player::create([
            'user_id' => $user->id,
            'nickname' => 'Miner',
            'city_id' => $city->id,
            'money' => 1000,
            'lvl' => 1,
            'exp' => 0,
            'max_exp' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('map.index'));

        $response->assertOk();
        $response->assertSee('Metroville');
        $response->assertSee('Iron Ore');
        $response->assertSee('Metroville Iron Mine');
        $response->assertSee('Iron mine');
    }

    public function test_map_page_highlights_current_player_location(): void
    {
        $this->seed(ResourceSeeder::class);
        $this->seed(CitiesSeeder::class);
        $this->seed(LocationSeeder::class);

        $city = City::first();
        $location = Location::where('city_id', $city->id)->firstOrFail();

        $user = User::factory()->create();
        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'Surveyor',
            'city_id' => null,
            'current_location_id' => $location->id,
            'money' => 1000,
            'lvl' => 1,
            'exp' => 0,
            'max_exp' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('map.index'));

        $response->assertOk();
        $response->assertSee($location->name);
        $response->assertSee('You are here');
    }

    public function test_map_page_supports_ukrainian_localization(): void
    {
        $this->seed(ResourceSeeder::class);
        $this->seed(CitiesSeeder::class);
        $this->seed(LocationSeeder::class);

        $user = User::factory()->create();
        Player::create([
            'user_id' => $user->id,
            'nickname' => 'Kozak',
            'city_id' => City::first()->id,
            'money' => 1000,
            'lvl' => 1,
            'exp' => 0,
            'max_exp' => 100,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['locale' => 'uk'])
            ->get(route('map.index'));

        $response->assertOk();
        $response->assertSee('Карта світу');
        $response->assertSee('Залізнична мережа');
        $response->assertSee('Профіцитні ресурси');
        $response->assertSee('Виробничі локації');
    }

    public function test_map_does_not_draw_links_for_nonexistent_routes(): void
    {
        $this->seed(ResourceSeeder::class);

        $connectedCity = City::create([
            'name' => 'Linkhaven',
            'level' => 1,
            'max_level' => 10,
        ]);

        $isolatedCity = City::create([
            'name' => 'Lonewatch',
            'level' => 1,
            'max_level' => 10,
        ]);

        $resource = Resource::where('slug', 'iron-ore')->firstOrFail();

        $connectedLocation = Location::create([
            'name' => 'Linkhaven Iron Mine',
            'slug' => 'linkhaven-iron-mine',
            'city_id' => $connectedCity->id,
            'type' => 'iron_mine',
        ]);

        LocationResource::create([
            'location_id' => $connectedLocation->id,
            'resource_id' => $resource->id,
            'production_rate_per_hour' => 10.00,
            'max_capacity' => 1000,
            'current_amount' => 100,
        ]);

        // Real route for the connected location only.
        CityRoute::create([
            'from_city_id' => $connectedCity->id,
            'to_location_id' => $connectedLocation->id,
            'type' => 'city_to_location',
            'distance_km' => 50,
        ]);

        // A location that has no matching CityRoute at all.
        $orphanLocation = Location::create([
            'name' => 'Lonewatch Ghost Mine',
            'slug' => 'lonewatch-ghost-mine',
            'city_id' => $isolatedCity->id,
            'type' => 'iron_mine',
        ]);

        LocationResource::create([
            'location_id' => $orphanLocation->id,
            'resource_id' => $resource->id,
            'production_rate_per_hour' => 10.00,
            'max_capacity' => 1000,
            'current_amount' => 100,
        ]);

        // No city_to_city route between $connectedCity and $isolatedCity exists.

        $user = User::factory()->create();
        Player::create([
            'user_id' => $user->id,
            'nickname' => 'Surveyor',
            'city_id' => $connectedCity->id,
            'money' => 1000,
            'lvl' => 1,
            'exp' => 0,
            'max_exp' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('map.index'));

        $response->assertOk();

        $mapNodes = $response->viewData('mapNodes');
        $mapLinks = $response->viewData('mapLinks');

        $this->assertArrayHasKey($connectedCity->id, $mapNodes);
        $this->assertArrayNotHasKey($isolatedCity->id, $mapNodes, 'Isolated city with no real route must not be drawn on the map.');

        $this->assertArrayHasKey($connectedLocation->id, $mapNodes[$connectedCity->id]['locations']);

        foreach ($mapLinks as $link) {
            $this->assertNotEquals($isolatedCity->id, $link['id'] ?? null);
        }
    }

    public function test_navigation_includes_map_link(): void
    {
        $this->seed(ResourceSeeder::class);
        $this->seed(CitiesSeeder::class);

        $user = User::factory()->create();
        Player::create([
            'user_id' => $user->id,
            'nickname' => 'Traveler',
            'city_id' => City::first()->id,
            'money' => 1000,
            'lvl' => 1,
            'exp' => 0,
            'max_exp' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('map.index'));
        $response->assertSee('Map');
    }
}
