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
                'has_saloon' => rand(0, 1) === 1,
                'level' => 1,
                'max_level' => 10
            ]);

            $resourceAmountPerCity = 3;
            $counter = 0;

            $allResources = $allResources->shuffle();

            // Тепер, для кожного щойно створеного або знайденого міста,
            // додаємо зв'язки з ресурсами
            foreach ($allResources as $resource) {
                if ($counter >= $resourceAmountPerCity) break;
                // Встановлюємо початкові значення для кожного ресурсу в цьому місті
                // Можете варіювати quantity/base_quantity для створення початкового дефіциту/профіциту
                // Це також може бути визначено через фабрики або складнішу логіку
                switch ($counter) {
                    case 0: {$is_surplus = true; $is_deficit = false; break;}
                    case 1: {$is_surplus = false; $is_deficit = true; break;}
                    case 2: {$is_surplus = false; $is_deficit = false; break;}
                }
                $city->resources()->firstOrCreate(
                    ['resource_id' => $resource->id],
                    [
                        'quantity' =>           $is_surplus ? 1500 : ($is_deficit ? 500 : 1000),    // Початкова кількість (може бути випадковою)
                        'base_quantity' =>      1000,                       // Базова кількість
                        'buy_price' =>          $resource->base_price,      // Базова ціна купівлі (буде коригуватися множником)
                        'sell_price' =>         $resource->base_price,      // Базова ціна продажу
                        'price_multiplier' =>   1.0,                        // Початковий множник ціни
                        'is_surplus' =>         $is_surplus,                // Чи цього ресурсу в достатку
                        'is_deficit' =>         $is_deficit,                // Чи є дефіцит цього ресурсу
                    ]
                );
                $counter++;
            }
        }

        City::whereIn('name', ['Ironforge', 'Stormhelm', 'Sunspire'])->update(['has_workshop' => true]);
        City::whereIn('name', ['Silverbrook', 'Sunspire'])->update(['has_shop' => true]);
    }
}
