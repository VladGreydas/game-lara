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
            5 => 'Nagihara',
            6 => 'Frostgate',
            7 => 'Dreadmoor',
            8 => 'Ebonreach',
            9 => 'Thornhall',
            10 => 'Sunspire',
            11 => 'Goldmere',
            12 => 'Blackwood',
            13 => 'Highspire',
            14 => 'Mistvale',
            15 => 'Copperfield',
        ];

        $allResources = Resource::all();

        foreach ($cityNames as $id => $name) {
            $city = City::create([
                'id' => $id,
                'name' => $name,
                'has_workshop' => rand(0, 1) === 1,
                'has_shop' => 1,
                'has_saloon' => rand(0, 1) === 1,
                'level' => 1,
                'max_level' => 10
            ]);

            $cityResources = $allResources->shuffle()->values();

            $surplusResource = $cityResources->first();

            // Якщо профіцитний ресурс - T1 (напр. Planks), дефіцитним має бути
            // відповідний T0 ресурс, з якого він виробляється (напр. Wood).
            $deficitResource = null;
            if ($surplusResource->tier === '1' && $surplusResource->produced_from) {
                $deficitResource = $cityResources->firstWhere('slug', $surplusResource->produced_from);
            }

            if (!$deficitResource) {
                $deficitResource = $cityResources->where('id', '!=', $surplusResource->id)->first();
            }

            // Тепер, для кожного щойно створеного або знайденого міста,
            // додаємо зв'язки з ресурсами
            foreach ($cityResources as $resource) {
                // Встановлюємо початкові значення для кожного ресурсу в цьому місті
                // Можете варіювати quantity/base_quantity для створення початкового дефіциту/профіциту
                $is_surplus = $resource->id === $surplusResource->id;
                $is_deficit = !$is_surplus && $resource->id === $deficitResource->id;

                $city->resources()->firstOrCreate(
                    ['resource_id' => $resource->id],
                    [
                        'quantity' =>           $is_surplus ? 1500 : ($is_deficit ? 500 : 1000),    // Початкова кількість (може бути випадковою)
                        'base_quantity' =>      1000,                           // Базова кількість
                        'buy_price' =>          $resource->base_price,          // Базова ціна купівлі (буде коригуватися множником)
                        'sell_price' =>         $resource->base_price / 1.5,    // Базова ціна продажу
                        'price_multiplier' =>   1.0,                            // Початковий множник ціни
                        'is_surplus' =>         $is_surplus,                    // Чи цього ресурсу в достатку
                        'is_deficit' =>         $is_deficit,                    // Чи є дефіцит цього ресурсу
                    ]
                );
            }
        }

        City::whereIn('name', ['Ironforge', 'Nagihara', 'Sunspire'])->update(['has_workshop' => true]);
    }
}
