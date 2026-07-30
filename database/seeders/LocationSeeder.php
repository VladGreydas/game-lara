<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = \Config::get('locations');

        foreach ($locations as $location) {
            $location['slug'] = Str::slug($location['name']);
            Location::firstOrCreate(
                $location
            );
        }
    }
}
