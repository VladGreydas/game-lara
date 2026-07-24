<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Resource; // Не забудьте імпортувати модель Resource

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = \Config::get('resource');

        foreach ($resources as $resourceData) {
            Resource::firstOrCreate(
                ['slug' => $resourceData['slug']], // Шукаємо за slug, щоб уникнути дублікатів
                $resourceData
            );
        }
    }
}
