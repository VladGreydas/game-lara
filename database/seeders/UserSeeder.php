<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Test Conductor',
            'email' => 'driver@train.com',
            'password' => Hash::make('password'), // пароль для входу: password
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
