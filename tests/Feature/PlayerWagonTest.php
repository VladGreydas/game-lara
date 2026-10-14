<?php

namespace Tests\Feature;

use App\Models\CargoWagon;
use App\Models\CargoWagonResource;
use App\Models\City;
use App\Models\Player;
use App\Models\Resource;
use App\Models\User;
use App\Models\Wagon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerWagonTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_page_displays_cargo_wagon_contents_when_it_contains_resources(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Capital City', 'slug' => 'capital-city', 'level' => 1]);

        $iron = Resource::create([
            'name' => 'Iron Ore',
            'slug' => 'iron-ore',
            'description' => 'Raw iron ore',
            'base_price' => 15,
        ]);

        $wood = Resource::create([
            'name' => 'Wood Logs',
            'slug' => 'wood-logs',
            'description' => 'Timber',
            'base_price' => 10,
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'Conductor',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $city->id,
        ]);

        $cargoWagon = $player->train->wagons()->where('type', 'cargo')->first()->cargo_wagon;

        CargoWagonResource::create([
            'cargo_wagon_id' => $cargoWagon->id,
            'resource_id' => $iron->id,
            'quantity' => 5,
        ]);

        CargoWagonResource::create([
            'cargo_wagon_id' => $cargoWagon->id,
            'resource_id' => $wood->id,
            'quantity' => 3,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('player.index'));

        $response->assertOk();
        $response->assertSee('Iron Ore');
        $response->assertSee('5');
        $response->assertSee('Wood Logs');
        $response->assertSee('3');
        $response->assertDontSee(__('resource.you_have_no_resources_yet'));
    }

    public function test_player_page_displays_empty_message_when_cargo_wagon_has_no_resources(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Capital City', 'slug' => 'capital-city', 'level' => 1]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'EmptyConductor',
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
        $response->assertSee(__('resource.you_have_no_resources_yet'));
    }
}
