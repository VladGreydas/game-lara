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
            // Виключити поля конфігурації які не існують в таблиці resources
            $dataForDb = array_diff_key(
                $resourceData,
                array_flip(['processing_ratio', 'second_input', 'second_input_ratio'])
            );

            Resource::firstOrCreate(
                ['slug' => $resourceData['slug']],
                $dataForDb
            );
        }
    }
}
