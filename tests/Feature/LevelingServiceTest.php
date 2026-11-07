<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Player;
use App\Models\User;
use App\Services\LevelingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createPlayer(array $overrides = []): Player
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Base City', 'slug' => 'base-city-' . uniqid(), 'x' => 0, 'y' => 0, 'population' => 1000]);

        return Player::create(array_merge([
            'user_id' => $user->id,
            'nickname' => 'Leveler' . uniqid(),
            'money' => 1000,
            'exp' => 0,
            'max_exp' => 100,
            'lvl' => 1,
            'stamina' => 100,
            'max_stamina' => 100,
            'city_id' => $city->id,
        ], $overrides));
    }

    public function test_adding_experience_below_threshold_does_not_level_up(): void
    {
        $player = $this->createPlayer();
        $service = app(LevelingService::class);

        $levelsGained = $service->addExperience($player, 50, 'contract');

        $this->assertSame(0, $levelsGained);
        $this->assertEquals(1, $player->lvl);
        $this->assertEquals(50, $player->exp);
        $this->assertEquals(100, $player->max_exp);
    }

    public function test_adding_enough_experience_levels_up_once_and_carries_over_leftover_exp(): void
    {
        $player = $this->createPlayer(['exp' => 0, 'max_exp' => 100, 'lvl' => 1, 'money' => 1000, 'max_stamina' => 100]);
        $service = app(LevelingService::class);

        $levelsGained = $service->addExperience($player, 120, 'contract');

        $this->assertSame(1, $levelsGained);
        $this->assertEquals(2, $player->lvl);
        $this->assertEquals(20, $player->exp);
        $this->assertEquals((int) round(100 * 2 ** 1.3), $player->max_exp);
        $this->assertEquals(105, $player->max_stamina);
        $this->assertEquals(1000 + 100 + 2 * 50, $player->money);
    }

    public function test_adding_a_large_amount_of_experience_levels_up_multiple_times(): void
    {
        $player = $this->createPlayer(['exp' => 0, 'max_exp' => 100, 'lvl' => 1, 'money' => 0, 'max_stamina' => 100]);
        $service = app(LevelingService::class);

        $levelsGained = $service->addExperience($player, 1000, 'contract');

        $this->assertGreaterThanOrEqual(2, $levelsGained);
        $this->assertGreaterThan(1, $player->lvl);
        $this->assertLessThan($player->max_exp, $player->exp);
        $this->assertGreaterThan(100, $player->max_stamina);
        $this->assertGreaterThan(0, $player->money);
    }

    public function test_adding_zero_or_negative_experience_does_nothing(): void
    {
        $player = $this->createPlayer();
        $service = app(LevelingService::class);

        $levelsGained = $service->addExperience($player, 0, 'contract');

        $this->assertSame(0, $levelsGained);
        $this->assertEquals(0, $player->exp);
        $this->assertEquals(1, $player->lvl);
    }
}
