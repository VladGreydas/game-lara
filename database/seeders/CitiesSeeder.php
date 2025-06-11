<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityRoute;
use App\Models\Resource;
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

        $allResources = Resource::all();

        foreach ($cityNames as $id => $name) {
            $city = City::create([
                'id' => $id,
                'name' => $name,
                'has_workshop' => rand(0, 1) === 1,
                'has_shop' => rand(0, 1) === 1,
            ]);

            // Тепер, для кожного щойно створеного або знайденого міста,
            // додаємо зв'язки з ресурсами
            foreach ($allResources as $resource) {
                // Встановлюємо початкові значення для кожного ресурсу в цьому місті
                // Можете варіювати quantity/base_quantity для створення початкового дефіциту/профіциту
                // Це також може бути визначено через фабрики або складнішу логіку
                $city->resources()->firstOrCreate(
                    ['resource_id' => $resource->id],
                    [
                        'quantity' =>           rand(800, 1200),    // Початкова кількість (може бути випадковою)
                        'base_quantity' =>      1000,               // Базова кількість
                        'buy_price' =>          rand(10, 20),       // Базова ціна купівлі (буде коригуватися множником)
                        'sell_price' =>         rand(8, 18),        // Базова ціна продажу
                        'price_multiplier' =>   1.0,                // Початковий множник ціни
                    ]
                );
            }
        }

        City::whereIn('name', ['Ironforge', 'Stormhelm', 'Sunspire'])->update(['has_workshop' => true]);
        City::whereIn('name', ['Silverbrook', 'Sunspire'])->update(['has_shop' => true]);
    }
}
