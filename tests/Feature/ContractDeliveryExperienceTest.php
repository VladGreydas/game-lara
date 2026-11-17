<?php

namespace Tests\Feature;

use App\Models\CargoWagonResource;
use App\Models\City;
use App\Models\Contract;
use App\Models\Player;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractDeliveryExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivering_a_contract_grants_experience_to_the_player(): void
    {
        $user = User::factory()->create();
        $originCity = City::create(['name' => 'Origin City', 'slug' => 'origin-city', 'x' => 0, 'y' => 0, 'population' => 1000]);
        $destinationCity = City::create(['name' => 'Destination City', 'slug' => 'destination-city', 'x' => 100, 'y' => 100, 'population' => 1000]);

        $resource = Resource::create([
            'name' => 'Iron Ore',
            'slug' => 'iron-ore',
            'description' => 'Raw iron ore',
            'base_price' => 15,
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'Deliverer',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $destinationCity->id,
        ]);

        $contract = Contract::create([
            'city_id' => $originCity->id,
            'destination_city_id' => $destinationCity->id,
            'resource_id' => $resource->id,
            'quantity' => 5,
            'reward' => 40,
            'status' => Contract::STATUS_ACCEPTED,
            'player_id' => $player->id,
            'accepted_at' => now(),
        ]);

        $cargoWagon = $player->train->wagons()->where('type', 'cargo')->first()->cargo_wagon;
        $cargoWagon->update(['capacity' => 10]);

        CargoWagonResource::create([
            'cargo_wagon_id' => $cargoWagon->id,
            'resource_id' => $resource->id,
            'quantity' => 5,
        ]);

        $result = $contract->deliverBy($player);

        $this->assertTrue($result);

        $player->refresh();
        $this->assertEquals(20, $player->exp); // max(10, floor(40/2)) = 20
        $this->assertEquals(1040, $player->money);
    }

    public function test_failed_delivery_does_not_grant_experience(): void
    {
        $user = User::factory()->create();
        $originCity = City::create(['name' => 'Origin City', 'slug' => 'origin-city', 'x' => 0, 'y' => 0, 'population' => 1000]);
        $destinationCity = City::create(['name' => 'Destination City', 'slug' => 'destination-city', 'x' => 100, 'y' => 100, 'population' => 1000]);

        $resource = Resource::create([
            'name' => 'Iron Ore',
            'slug' => 'iron-ore',
            'description' => 'Raw iron ore',
            'base_price' => 15,
        ]);

        $player = Player::create([
            'user_id' => $user->id,
            'nickname' => 'FailedDeliverer',
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'city_id' => $destinationCity->id,
        ]);

        // Player has not accepted the contract, so delivery should fail.
        $contract = Contract::create([
            'city_id' => $originCity->id,
            'destination_city_id' => $destinationCity->id,
            'resource_id' => $resource->id,
            'quantity' => 5,
            'reward' => 40,
            'status' => Contract::STATUS_AVAILABLE,
        ]);

        $result = $contract->deliverBy($player);

        $this->assertFalse($result);

        $player->refresh();
        $this->assertEquals(0, $player->exp);
        $this->assertEquals(1000, $player->money);
    }
}
