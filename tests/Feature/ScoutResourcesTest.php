<?php

namespace Tests\Feature;

use App\Models\CargoWagon;
use App\Models\CargoWagonResource;
use App\Models\City;
use App\Models\CityResource;
use App\Models\CityRoute;
use App\Models\Location;
use App\Models\LocationResource;
use App\Models\Player;
use App\Models\Resource;
use App\Models\User;
use App\Models\Wagon;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoutResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_page_displays_scout_action_button_when_not_traveling(): void
    {
        $user = User::factory()->create();
        $city = City::create([
            'name' => 'New London',
            'slug' => 'new-london',
            'x' => 0,
            'y' => 0,
            'population' => 1000,
            'is_capital' => true,
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'Captain',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('player.index'));

        $response->assertOk();
        $response->assertSee(route('player.scout', $player));
        $response->assertSee(__('player.scout_for_resources'));
    }

    public function test_player_page_does_not_display_scout_action_button_when_traveling(): void
    {
        $user = User::factory()->create();
        $city1 = City::create(['name' => 'City 1', 'slug' => 'city-1', 'x' => 0, 'y' => 0, 'population' => 1000]);
        $city2 = City::create(['name' => 'City 2', 'slug' => 'city-2', 'x' => 100, 'y' => 100, 'population' => 2000]);

        $route = CityRoute::create([
            'from_city_id' => $city1->id,
            'to_city_id' => $city2->id,
            'distance' => 100,
            'travel_time' => 3600,
            'danger_level' => 1,
            'type' => 'city_to_city',
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'Traveler',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'current_city_route_id' => $route->id,
            'travel_starts_at' => Carbon::now()->subMinutes(10),
            'travel_finishes_at' => Carbon::now()->addMinutes(50),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('player.index'));

        $response->assertOk();
        $response->assertDontSee(route('player.scout', $player));
    }

    public function test_player_scouts_city_proficient_resource_into_cargo_wagons(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Iron City', 'slug' => 'iron-city', 'level' => 1]);

        $iron = Resource::create([
            'name' => 'Iron Ore',
            'slug' => 'iron-ore',
            'description' => 'Raw iron ore',
            'base_price' => 15,
        ]);

        $wood = Resource::create([
            'name' => 'Wood',
            'slug' => 'wood',
            'description' => 'Lumber',
            'base_price' => 10,
        ]);

        // City has Iron as surplus (proficient) and Wood as non-surplus
        CityResource::create([
            'city_id' => $city->id,
            'resource_id' => $iron->id,
            'quantity' => 1500,
            'base_quantity' => 1000,
            'buy_price' => 15,
            'sell_price' => 10,
            'price_multiplier' => 0.7,
            'is_surplus' => true,
            'is_deficit' => false,
        ]);

        CityResource::create([
            'city_id' => $city->id,
            'resource_id' => $wood->id,
            'quantity' => 500,
            'base_quantity' => 1000,
            'buy_price' => 10,
            'sell_price' => 6,
            'price_multiplier' => 1.3,
            'is_surplus' => false,
            'is_deficit' => true,
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'CityScout',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('player.index'))
            ->post(route('player.scout', $player));

        $response->assertRedirect(route('player.index'));
        $response->assertSessionHas('success');

        $starterCargoWagon = $player->train->wagons()->where('type', 'cargo')->first()->cargo_wagon;
        $ironStored = CargoWagonResource::where('cargo_wagon_id', $starterCargoWagon->id)
            ->where('resource_id', $iron->id)
            ->first();

        $this->assertNotNull($ironStored);
        $this->assertGreaterThanOrEqual(1, $ironStored->quantity);
        $this->assertLessThanOrEqual(10, $ironStored->quantity);

        $woodStored = CargoWagonResource::where('cargo_wagon_id', $starterCargoWagon->id)
            ->where('resource_id', $wood->id)
            ->first();
        $this->assertNull($woodStored);
    }

    public function test_player_scouts_location_proficient_resource_into_cargo_wagons(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Base City', 'slug' => 'base-city']);

        $location = Location::create([
            'city_id' => $city->id,
            'name' => 'Coal Mine Outpost',
            'type' => 'coal_mine',
            'slug' => 'coal-mine-outpost',
        ]);

        $coal = Resource::create([
            'name' => 'Coal',
            'slug' => 'coal',
            'description' => 'Black coal',
            'base_price' => 10,
        ]);

        LocationResource::create([
            'location_id' => $location->id,
            'resource_id' => $coal->id,
            'production_rate_per_hour' => 10,
            'max_capacity' => 100,
            'current_amount' => 50,
            'last_produced_at' => Carbon::now(),
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'LocationScout',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => null,
            'current_location_id' => $location->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('player.index'))
            ->post(route('player.scout', $player));

        $response->assertRedirect(route('player.index'));
        $response->assertSessionHas('success');

        $starterCargoWagon = $player->train->wagons()->where('type', 'cargo')->first()->cargo_wagon;
        $coalStored = CargoWagonResource::where('cargo_wagon_id', $starterCargoWagon->id)
            ->where('resource_id', $coal->id)
            ->first();

        $this->assertNotNull($coalStored);
        $this->assertGreaterThanOrEqual(1, $coalStored->quantity);
        $this->assertLessThanOrEqual(10, $coalStored->quantity);
    }

    public function test_scout_distributes_across_multiple_cargo_wagons(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Iron City', 'slug' => 'iron-city']);

        $iron = Resource::create([
            'name' => 'Iron Ore',
            'slug' => 'iron-ore',
            'base_price' => 15,
        ]);

        CityResource::create([
            'city_id' => $city->id,
            'resource_id' => $iron->id,
            'is_surplus' => true,
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'MultiWagonScout',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
        ]);

        // Adjust starter cargo wagon to only have 4 remaining capacity
        $wagon1 = $player->train->wagons()->where('type', 'cargo')->first();
        $wagon1->cargo_wagon->update(['capacity' => 4]);

        // Add a second cargo wagon with capacity 10
        $wagon2 = Wagon::create([
            'train_id' => $player->train->id,
            'name' => 'Second Cargo Wagon',
            'type' => 'cargo',
            'weight' => 15,
            'armor' => 50,
            'max_armor' => 50,
            'lvl' => 1,
            'price' => 200,
            'upgrade_cost' => 100,
        ]);
        $cargoWagon2 = CargoWagon::create([
            'wagon_id' => $wagon2->id,
            'capacity' => 10,
        ]);

        // Scout exact amount 9: wagon1 should take 4, wagon2 should take 5
        $success = $player->scout(9);
        $this->assertTrue($success);

        $storedInWagon1 = CargoWagonResource::where('cargo_wagon_id', $wagon1->cargo_wagon->id)->first();
        $storedInWagon2 = CargoWagonResource::where('cargo_wagon_id', $cargoWagon2->id)->first();

        $this->assertEquals(4, $storedInWagon1->quantity);
        $this->assertEquals(5, $storedInWagon2->quantity);
    }

    public function test_player_cannot_scout_when_cargo_wagons_are_full(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Iron City', 'slug' => 'iron-city']);

        $iron = Resource::create([
            'name' => 'Iron Ore',
            'slug' => 'iron-ore',
            'base_price' => 15,
        ]);

        CityResource::create([
            'city_id' => $city->id,
            'resource_id' => $iron->id,
            'is_surplus' => true,
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'FullWagonScout',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
        ]);

        $wagon = $player->train->wagons()->where('type', 'cargo')->first();
        CargoWagonResource::create([
            'cargo_wagon_id' => $wagon->cargo_wagon->id,
            'resource_id' => $iron->id,
            'quantity' => 10, // Max capacity is 10
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('player.index'))
            ->post(route('player.scout', $player));

        $response->assertRedirect(route('player.index'));
        $response->assertSessionHas('error');
    }

    public function test_player_cannot_scout_for_resources_while_traveling(): void
    {
        $user = User::factory()->create();
        $city1 = City::create(['name' => 'City 1', 'slug' => 'city-1', 'x' => 0, 'y' => 0, 'population' => 1000]);
        $city2 = City::create(['name' => 'City 2', 'slug' => 'city-2', 'x' => 100, 'y' => 100, 'population' => 2000]);

        $route = CityRoute::create([
            'from_city_id' => $city1->id,
            'to_city_id' => $city2->id,
            'distance' => 100,
            'travel_time' => 3600,
            'danger_level' => 1,
            'type' => 'city_to_city',
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'Traveler',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'current_city_route_id' => $route->id,
            'travel_starts_at' => Carbon::now()->subMinutes(10),
            'travel_finishes_at' => Carbon::now()->addMinutes(50),
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('player.index'))
            ->post(route('player.scout', $player));

        $response->assertRedirect(route('player.index'));
        $response->assertSessionHas('error');
    }

    public function test_unauthenticated_user_cannot_scout(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'City 1', 'slug' => 'city-1', 'x' => 0, 'y' => 0, 'population' => 1000]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'Explorer',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
        ]);

        $response = $this->post(route('player.scout', $player));

        $response->assertForbidden();
    }

    public function test_player_cannot_scout_for_another_player(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $city = City::create(['name' => 'City 1', 'slug' => 'city-1', 'x' => 0, 'y' => 0, 'population' => 1000]);

        $player1 = Player::create([
            'user_id' => $user1->id,
            'nickname' => 'Explorer 1',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
        ]);

        $player2 = Player::create([
            'user_id' => $user2->id,
            'nickname' => 'Explorer 2',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
        ]);

        $response = $this
            ->actingAs($user1)
            ->post(route('player.scout', $player2));

        $response->assertForbidden();
    }

    public function test_player_page_displays_stamina(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'City 1', 'slug' => 'city-1', 'x' => 0, 'y' => 0, 'population' => 1000]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'StaminaPlayer',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
            'stamina' => 85,
            'max_stamina' => 100,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('player.index'));

        $response->assertOk();
        $response->assertSee(__('player.stamina'));
        $response->assertSee('85 / 100');
    }

    public function test_scout_deducts_5_stamina_points(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Iron City', 'slug' => 'iron-city', 'level' => 1]);

        $iron = Resource::create([
            'name' => 'Iron Ore',
            'slug' => 'iron-ore',
            'description' => 'Raw iron ore',
            'base_price' => 15,
        ]);

        CityResource::create([
            'city_id' => $city->id,
            'resource_id' => $iron->id,
            'is_surplus' => true,
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'StaminaScout',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
            'stamina' => 100,
            'max_stamina' => 100,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('player.scout', $player));

        $response->assertRedirect();
        $player->refresh();
        $this->assertEquals(95, $player->stamina);
    }

    public function test_player_cannot_scout_with_insufficient_stamina(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Iron City', 'slug' => 'iron-city', 'level' => 1]);

        $iron = Resource::create([
            'name' => 'Iron Ore',
            'slug' => 'iron-ore',
            'description' => 'Raw iron ore',
            'base_price' => 15,
        ]);

        CityResource::create([
            'city_id' => $city->id,
            'resource_id' => $iron->id,
            'is_surplus' => true,
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'TiredScout',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
            'stamina' => 4,
            'max_stamina' => 100,
            'last_stamina_regenerated_at' => Carbon::now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('player.index'))
            ->post(route('player.scout', $player));

        $response->assertRedirect(route('player.index'));
        $response->assertSessionHas('error', __('error.player.not_enough_stamina'));

        $player->refresh();
        $this->assertEquals(4, $player->stamina);

        $wagon = $player->train->wagons()->where('type', 'cargo')->first();
        $cargoWagonResource = CargoWagonResource::where('cargo_wagon_id', $wagon->cargo_wagon->id)->first();
        $this->assertNull($cargoWagonResource);
    }

    public function test_stamina_regenerates_over_time(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Iron City', 'slug' => 'iron-city', 'level' => 1]);

        $now = Carbon::now();
        Carbon::setTestNow($now);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'RegenScout',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
            'stamina' => 10,
            'max_stamina' => 100,
            'last_stamina_regenerated_at' => $now,
        ]);

        // Advance time by 3 minutes (180 seconds = +3 stamina points)
        Carbon::setTestNow($now->copy()->addMinutes(3));

        $player->regenerateStamina();
        $this->assertEquals(13, $player->stamina);

        // Advance time past max stamina capacity
        Carbon::setTestNow($now->copy()->addHours(3));
        $player->regenerateStamina();
        $this->assertEquals(100, $player->stamina);

        Carbon::setTestNow(); // Reset test time
    }
}
