<?php

namespace Database\Factories;

use App\Models\Weapon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Weapon>
 */
class WeaponFactory extends Factory
{
    protected $model = Weapon::class;

    public function definition(): array
    {
        return [
            'name' => 'Default',
            'type' => 'HMG',
            'damage' => 50,
            'weight' => 200,
            'price' => 800,
            'lvl' => 1,
            'upgrade_cost' => 100,
        ];
    }

    public function forShop(string $type = 'HMG', int $variant = 0): Factory
    {
        $baseStats = match ($type) {
            'HMG' => ['damage' => 50, 'price' => 500],
            'Cannon' => ['damage' => 100, 'price' => 1000],
            'Mortar' => ['damage' => 75, 'price' => 750],
            'Rocket Launcher' => ['damage' => 250, 'price' => 2500],
            'Flame Thrower' => ['damage' => 150, 'price' => 1500],
            default => ['damage' => 40, 'price' => 600],
        };

        return $this->state(function () use ($type, $variant, $baseStats) {
            return [
                'name' => "{$type} Mk" . ($variant + 1),
                'type' => $type,
                'damage' => $baseStats['damage'] + $variant * 15,
                'price' => $baseStats['price'] + $variant * 250,
                'lvl' => 1,
                'upgrade_cost' => 100,
            ];
        });
    }

    public static function generateShopWeapons(): Collection
    {
        $types = ['HMG', 'Cannon', 'Mortar', 'Rocket Launcher', 'Flame Thrower', 'Laser'];
        $collection = collect();

        foreach ($types as $type) {
            for ($i = 0; $i < 3; $i++) {
                $weapon = Weapon::factory()->forShop($type, $i)->make();
                $weapon->shop_uuid = (string) Str::uuid(); // для використання в shop view
                $collection->push($weapon);
            }
        }

        return $collection;
    }
}
