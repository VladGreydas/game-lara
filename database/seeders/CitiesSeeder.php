<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityRoute;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $cityNames = [
            1 => 'Ironforge',
            2 => 'Silverbrook',
            3 => 'Ashenvale',
            4 => 'Rivermoor',
            5 => 'Stormhelm',
            6 => 'Frostgate',
            7 => 'Dreadmoor',
            8 => 'Ebonreach',
            9 => 'Thornhall',
            10 => 'Sunspire',
        ];

        foreach ($cityNames as $id => $name) {
            City::create([
                'id' => $id,
                'name' => $name,
                'has_workshop' => rand(0, 1) === 1,
                'has_shop' => rand(0, 1) === 1,
            ]);
        }

        City::whereIn('name', ['Ironforge', 'Stormhelm', 'Sunspire'])->update(['has_workshop' => true]);
        City::whereIn('name', ['Silverbrook', 'Sunspire'])->update(['has_shop' => true]);
    }
}
