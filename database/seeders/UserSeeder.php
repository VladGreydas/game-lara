<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Player;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator']
        );

        $userRole = Role::firstOrCreate(
            ['name' => 'user'],
            ['description' => 'Regular user']
        );

        // Create admin user
        $user = User::create([
            'name' => 'Test Conductor',
            'email' => 'driver@train.com',
            'password' => Hash::make('password'), // пароль для входу: password
        ]);

        // Assign admin role using direct DB insert
        DB::table('model_has_roles')->insert([
            'role_id' => $adminRole->id,
            'model_id' => $user->id,
            'model_type' => User::class,
        ]);

        $firstCity = City::first();

        Player::create([
            'user_id' => $user->id,
            'nickname' => 'Test Conductor',
            'city_id' => $firstCity ? $firstCity->id : 1,
            'money' => 1000,
            'lvl' => 1,
            'exp' => 0,
            'max_exp' => 100,
            'stamina' => 100,
            'max_stamina' => 100,
        ]);
    }
}
