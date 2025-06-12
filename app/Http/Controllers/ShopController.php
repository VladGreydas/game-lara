<?php

namespace App\Http\Controllers;

use App\Factories\WagonShopFactory;
use App\Models\CargoWagon;
use App\Models\CargoWagonResource;
use App\Models\City;
use App\Models\CityResource;
use App\Models\Locomotive;
use App\Models\Player;
use App\Models\Resource;
use App\Models\Wagon;
use App\Models\Weapon;
use App\Models\WeaponWagon;
use App\Services\ShopSellService;
use App\Services\ShopSetupService;
use Database\Factories\CargoWagonFactory;
use Database\Factories\WeaponWagonFactory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShopController extends Controller
{
    // Інжектуємо сервіс через конструктор (рекомендований спосіб)
    public function __construct(protected ShopSetupService $shopSetupService, protected ShopSellService $shopSellService)
    {

    }

    public function index(Request $request)
    {
        /** @var Player $player */
        $player = $request->user()->player;
        /** @var City $city */
        $city = $player->city;

        if (!$this->shopSetupService->hasSessionInventory()) {
            $this->shopSetupService->generateInventory();
        }

        $shopInventory = $this->shopSetupService->getInventory();

        // Тепер у $shopInventory будуть такі ключі: 'locomotives', 'wagons', 'weapons'
        $locomotives = $shopInventory['locomotives'];
        $wagons = $shopInventory['wagons'];
        $weapons = $shopInventory['weapons'];

        $weaponWagonsForMounting = $player->train->checkAvailableWeaponWagons();
        $weaponWagonsForMounting = collect($weaponWagonsForMounting);

        $playerWagonsForSale = \App\Models\Wagon::whereHas('train', function ($query) use ($player) {
            $query->where('player_id', $player->id);
        })
            ->get();
        $playerWeaponsForSale = \App\Models\Weapon::whereHas('weapon_wagon.wagon.train', function ($query) use ($player) {
            $query->where('player_id', $player->id);
        })
            ->get();

        // --- ДОДАНО: ЛОГІКА ДЛЯ РЕСУРСІВ ---

        // 1. Отримуємо ресурси, які можна купити в поточному місті
        // Жадібне завантаження resource, щоб уникнути N+1 проблеми у Blade
        $cityResources = $city->resources()->with('resource')->get();

        // 2. Розраховуємо загальну вантажну ємність гравця та доступний простір
        $totalCargoCapacity = 0;
        $currentCargoCapacity = 0;
        $playerCargoWagonResources = new Collection(); // Колекція для ресурсів гравця, які можна продати

        if ($player->train) {
            foreach ($player->train->wagons as $wagon) {
                if ($wagon->isCargo() && $wagon->cargo_wagon) {
                    // Завантажуємо ресурси для кожного вантажного вагона
                    $wagon->cargo_wagon->load('resources.resource'); // Завантажуємо ресурси та їх визначення
                    $totalCargoCapacity += $wagon->cargo_wagon->capacity;
                    $currentCargoCapacity += $wagon->cargo_wagon->getCurrentCapacity();

                    // Додаємо ресурси цього вантажного вагона до загальної колекції ресурсів гравця
                    $playerCargoWagonResources = $playerCargoWagonResources->concat($wagon->cargo_wagon->resources);
                }
            }
        }
        $availableCargoSpace = $totalCargoCapacity - $currentCargoCapacity;

        // Групуємо ресурси гравця за resource_id для відображення загальної кількості одного типу ресурсу
        // Це потрібно для секції "Sell Resources"
        $groupedPlayerCargoWagonResources = $playerCargoWagonResources->groupBy('resource_id')->map(function ($items) {
            $firstItem = $items->first();
            // Повертаємо об'єкт, схожий на CargoWagonResource, але з агрегованою кількістю
            return (object)[
                'resource_id' => $firstItem->resource_id,
                'resource' => $firstItem->resource, // Залишаємо об'єкт Resource
                'quantity' => $items->sum('quantity'), // Сумуємо кількості
                // Можливо, тут знадобиться cargo_wagon_resource_id для форми продажу.
                // Наразі залишаємо його без конкретного ID, оскільки продаватимемо "від загальної кількості"
                // і потім розподілятимемо продаж між кількома CargoWagonResource, якщо необхідно.
                // Для форми продажу краще передавати resource_id і вже в контролері шукати CargoWagonResource
            ];
        });

        // --- КІНЕЦЬ: ЛОГІКА ДЛЯ РЕСУРСІВ ---


        return view('city.shop.index', compact(
            'player',
            'city',
            'locomotives',
            'wagons',
            'weapons',
            'weaponWagonsForMounting',
            'playerWagonsForSale',
            'playerWeaponsForSale',
            'cityResources',                 // ДОДАНО: Ресурси для купівлі
            'playerCargoWagonResources',     // ДОДАНО: Ресурси гравця для продажу (НЕ згрупована колекція CargoWagonResource)
            'groupedPlayerCargoWagonResources', // ДОДАНО: Згруповані ресурси гравця для відображення
            'availableCargoSpace',           // ДОДАНО: Доступний вантажний простір
            'totalCargoCapacity'             // ДОДАНО: Загальна вантажна ємність
        ));
    }

    public function buyLocomotive(string $uuid)
    {
        $player = Auth::user()->player;

        /** @var Locomotive|null $shopLoco */
        $shopLoco = session("shop.locomotives.$uuid");

        if (!$shopLoco) {
            return back()->with('error', 'Locomotive not available.');
        }

        $currentLoco = $player->train->locomotive;

        $currentValue = $currentLoco?->price ?? 0;
        $priceDifference = $shopLoco->price - $currentValue;

        if ($player->money < $priceDifference) {
            return back()->with('error', 'Not enough money, even after trading in your current locomotive.');
        }

        // Видалення старого локомотива (якщо є)
        if ($currentLoco) {
            $currentLoco->delete();
        }

        // Купівля нового
        $newLoco = new Locomotive($shopLoco->toArray());
        $newLoco->train_id = $player->train->id;
        $newLoco->save();

        // Віднімання різниці в ціні
        $player->decrement('money', $priceDifference);

        // Видалення із сесії
        session()->forget("shop.locomotives.$uuid");

        return back()->with('success', 'Locomotive successfully purchased and installed!');
    }

    public function buyWagon(string $uuid)
    {
        $player = Auth::user()->player;
        $train = $player->train;

        /** @var Wagon $wagon */
        $wagon = session("shop.wagons.$uuid");

        if (!$wagon) {
            return back()->with('error', 'Wagon not found in shop.');
        }

        // Перевірка балансу
        if ($player->money < $wagon->price) {
            return back()->with('error', 'Not enough money.');
        }

        // Створення основного запису
        $wagonModel = Wagon::create([
            'train_id' => $train->id,
            'name' => $wagon->name,
            'type' => $wagon->type,
            'weight' => $wagon->weight,
            'armor' => $wagon->armor,
            'max_armor' => $wagon->max_armor,
            'lvl' => 1,
            'price' => $wagon->price,
            'upgrade_cost' => $wagon->upgrade_cost,
        ]);

        // Дочірній тип
        if ($wagon->type === 'cargo' && isset($wagon->cargo_data)) {
            CargoWagon::create([
                'wagon_id' => $wagonModel->id,
                'capacity' => $wagon->cargo_data['capacity'],
            ]);
        }

        if ($wagon->type === 'weapon' && isset($wagon->weapon_data)) {
            WeaponWagon::create([
                'wagon_id' => $wagonModel->id,
                'slots_available' => $wagon->weapon_data['slots_available'],
            ]);
        }

        // Зняти кошти
        $player->money -= $wagon->price;
        $player->save();

        return back()->with('success', 'Wagon purchased successfully.');
    }

    public function sellWagon(Request $request, Wagon $wagon) // <-- Тепер приймає об'єкт Wagon
    {
        /** @var Player $player */
        $player = Auth::user()->player;

        // Перевірка дозволу: чи належить цей вагон гравцеві
        // Laravel автоматично видасть 404, якщо вагон не знайдено за ID,
        // але ми повинні перевірити, чи він належить поточному гравцеві.
        if ($wagon->train->player->id !== $player->id) {
            throw new AuthorizationException('You do not own this wagon.');
        }

        try {
            $destinationWeaponWagonId = $request->input('destination_weapon_wagon_id');

            // Передаємо об'єкт Wagon напряму в сервіс
            $this->shopSellService->sellWagon($player, $wagon, $destinationWeaponWagonId);

            return back()->with('success', 'Wagon sold successfully!');

        } catch (ValidationException $e) {
            return back()->with('error', 'Validation error: ' . $e->getMessage())->withErrors($e->errors());
        } catch (AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error("Sell wagon error: " . $e->getMessage(), ['player_id' => $player->id, 'wagon_id' => $wagon->id]);
            return back()->with('error', 'An unexpected error occurred during wagon sale. ' . $e->getMessage());
        }
    }

    public function buyWeapon(Request $request, string $shop_uuid)
    {
        $player = Auth::user()->player;
        $weaponData = session("shop.weapons.$shop_uuid");;

        if (!$weaponData) {
            return back()->with('error', 'Weapon not found in shop.');
        }

        // Валідація вибору вагона
        $validated = $request->validate([
            'weapon_wagon_id' => ['required', 'exists:weapon_wagons,id'],
        ]);

        $weaponWagon = WeaponWagon::with('wagon')->findOrFail($validated['weapon_wagon_id']);

        // Перевірка, чи цей вагон належить потягу гравця
        if ($weaponWagon->wagon->train_id !== $player->train->id) {
            abort(403, 'You do not own this weapon wagon.');
        }

        // Перевірка балансу
        if ($player->money < $weaponData['price']) {
            return back()->with('error', 'Not enough money.');
        }

        // Покупка
        $player->money -= $weaponData['price'];
        $player->save();

        // Створення нової зброї
        $weapon = Weapon::create([
            'name' => $weaponData['name'],
            'type' => $weaponData['type'],
            'damage' => $weaponData['damage'],
            'weight' => $weaponData['weight'],
            'price' => $weaponData['price'],
            'lvl' => 1,
            'upgrade_cost' => $weaponData['upgrade_cost'],
            'weapon_wagon_id' => $weaponWagon->id,
        ]);

        $weaponWagon->decrement('slots_available');

        return back()->with('success', "Weapon {$weapon->name} successfully mounted on wagon #{$weaponWagon->id}.");
    }

    public function sellWeapon(Request $request, Weapon $weapon) // <-- Тепер приймає об'єкт Weapon
    {
        /** @var Player $player */
        $player = Auth::user()->player;

        // Перевірка дозволу: чи належить ця зброя гравцеві
        if ($weapon->weapon_wagon->wagon->train->player->id !== $player->id) {
            throw new AuthorizationException('You do not own this weapon.');
        }

        try {
            // Валідації тут немає, оскільки ID зброї приходить через URL
            // і інших параметрів поки не передбачається.

            // Передаємо об'єкт Weapon напряму в сервіс
            $this->shopSellService->sellWeapon($player, $weapon);

            return back()->with('success', 'Weapon sold successfully!');

        } catch (AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error("Sell weapon error: " . $e->getMessage(), ['player_id' => $player->id, 'weapon_id' => $weapon->id]);
            return back()->with('error', 'An unexpected error occurred during weapon sale. ' . $e->getMessage());
        }
    }

    // --- НОВИЙ МЕТОД: buyResource ---
    public function buyResource(Request $request, Resource $resource)
    {
        /** @var Player $player */
        $player = Auth::user()->player;
        $city = $player->city;

        // 1. Валідація вхідних даних
        $validated = $request->validate([
            'city_resource_id' => ['required', 'integer', 'exists:city_resources,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cityResource = CityResource::with('resource')->findOrFail($validated['city_resource_id']);

        // 2. Перевірки ресурсів
        // Перевірка, чи CityResource належить поточному місту гравця
        if ($cityResource->city_id !== $city->id) {
            throw new AuthorizationException('This resource is not available in your current city.');
        }

        // Перевірка, чи ресурс в URL відповідає ресурсу з city_resource_id
        if ($cityResource->resource->slug !== $resource->slug) {
            return back()->with('error', 'Mismatched resource ID and slug.');
        }

        // Перевірка наявності достатньої кількості в місті
        if ($validated['quantity'] > $cityResource->quantity) {
            return back()->with('error', 'Not enough ' . $resource->name . ' available in the city.');
        }

        // Розрахунок вартості
        $totalCost = $cityResource->getCurrentBuyPrice() * $validated['quantity'];

        // Перевірка грошей гравця
        if ($player->money < $totalCost) {
            return back()->with('error', 'You do not have enough money to buy this amount of ' . $resource->name . '.');
        }

        // Перевірка вільного місця у вантажних вагонах
        $availableCargoSpace = 0;
        if ($player->train) {
            $player->train->load('wagons.cargo_wagon'); // Завантажуємо cargo_wagon для кожного вагона
            foreach ($player->train->wagons as $wagon) {
                if ($wagon->isCargo() && $wagon->cargo_wagon) {
                    $availableCargoSpace += $wagon->cargo_wagon->capacity - $wagon->cargo_wagon->getCurrentCapacity();
                }
            }
        }

        if ($validated['quantity'] > $availableCargoSpace) {
            return back()->with('error', 'Not enough free cargo space in your train for this amount of ' . $resource->name . '.');
        }

        // --- 3. Виконання транзакції ---
        DB::transaction(function () use ($player, $cityResource, $validated, $totalCost, $resource) {
            // 3.1. Зняття грошей з гравця
            $player->decrement('money', $totalCost);

            // 3.2. Зменшення кількості ресурсу в місті
            $cityResource->decrement('quantity', $validated['quantity']);
            // Оновлення price_multiplier відбудеться автоматично через хук "saving" у CityResource

            // 3.3. Додавання ресурсів до вантажних вагонів гравця
            $remainingQuantity = $validated['quantity'];
            if ($player->train) {
                $player->train->load('wagons.cargo_wagon.resources'); // Перезавантажуємо, щоб мати актуальні ресурси

                foreach ($player->train->wagons as $wagon) {
                    if ($wagon->isCargo() && $wagon->cargo_wagon && $remainingQuantity > 0) {
                        $cargoWagon = $wagon->cargo_wagon;
                        $wagonFreeSpace = $cargoWagon->capacity - $cargoWagon->getCurrentCapacity();

                        if ($wagonFreeSpace > 0) {
                            $quantityToAdd = min($remainingQuantity, $wagonFreeSpace);

                            // Шукаємо, чи вже є такий ресурс у цьому вагоні
                            $existingCargoResource = $cargoWagon->resources()
                                ->where('resource_id', $resource->id)
                                ->first();

                            if ($existingCargoResource) {
                                $existingCargoResource->increment('quantity', $quantityToAdd);
                            } else {
                                CargoWagonResource::create([
                                    'cargo_wagon_id' => $cargoWagon->id,
                                    'resource_id' => $resource->id,
                                    'quantity' => $quantityToAdd,
                                ]);
                            }
                            $remainingQuantity -= $quantityToAdd;
                        }
                    }
                }
            }

            // Перевірка, чи всі ресурси були додані (на випадок логічної помилки)
            if ($remainingQuantity > 0) {
                throw new \Exception('Failed to fully add resources to train cargo. Remaining: ' . $remainingQuantity);
            }
        });

        return back()->with('success', 'Successfully purchased ' . $validated['quantity'] . ' ' . $resource->name . '.');
    }

    // --- НОВИЙ МЕТОД: sellResource ---
    public function sellResource(Request $request, Resource $resource)
    {
        /** @var Player $player */
        $player = Auth::user()->player;
        $city = $player->city;

        // 1. Валідація вхідних даних
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        // 2. Перевірка ресурсів
        // Знаходимо CityResource для поточного міста та даного ресурсу
        $cityResource = CityResource::where('city_id', $city->id)
            ->where('resource_id', $resource->id)
            ->first();

        if (!$cityResource) {
            return back()->with('error', 'This resource cannot be sold in this city.');
        }

        // Перевіряємо, чи достатньо ресурсів у гравця для продажу (сумуємо по всіх вагонах)
        $playerResourceQuantity = 0;
        $playerCargoWagonResourcesForThisType = collect(); // Зберігаємо конкретні CargoWagonResource
        // для подальшого зменшення

        if ($player->train) {
            $player->train->load(['wagons.cargo_wagon.resources' => function($query) use ($resource) {
                $query->where('resource_id', $resource->id); // Завантажуємо тільки потрібні ресурси
            }]);

            foreach ($player->train->wagons as $wagon) {
                if ($wagon->isCargo() && $wagon->cargo_wagon) {
                    foreach ($wagon->cargo_wagon->resources as $cargoWagonResource) {
                        $playerResourceQuantity += $cargoWagonResource->quantity;
                        $playerCargoWagonResourcesForThisType->push($cargoWagonResource);
                    }
                }
            }
        }

        if ($validated['quantity'] > $playerResourceQuantity) {
            return back()->with('error', 'You do not have ' . $validated['quantity'] . ' ' . $resource->name . ' to sell.');
        }

        // Розрахунок вартості продажу
        $totalSellPrice = $cityResource->getCurrentSellPrice() * $validated['quantity'];

        // --- 3. Виконання транзакції ---
        DB::transaction(function () use ($player, $cityResource, $validated, $totalSellPrice, $playerCargoWagonResourcesForThisType) {
            // 3.1. Додавання грошей гравцю
            $player->increment('money', $totalSellPrice);

            // 3.2. Збільшення кількості ресурсу в місті
            $cityResource->increment('quantity', $validated['quantity']);
            // Оновлення price_multiplier відбудеться автоматично

            // 3.3. Зменшення кількості ресурсів у вантажних вагонах гравця
            $remainingQuantityToSell = $validated['quantity'];

            // Сортуємо вагони за кількістю ресурсу (можливо, не потрібно, але може допомогти в оптимізації)
            // Або просто проходимо по них і віднімаємо
            foreach ($playerCargoWagonResourcesForThisType as $cargoWagonResource) {
                if ($remainingQuantityToSell <= 0) {
                    break;
                }

                $quantityToDecrement = min($remainingQuantityToSell, $cargoWagonResource->quantity);
                $cargoWagonResource->decrement('quantity', $quantityToDecrement);
                $remainingQuantityToSell -= $quantityToDecrement;

                // Якщо кількість стала 0, видаляємо запис
                if ($cargoWagonResource->quantity <= 0) {
                    $cargoWagonResource->delete();
                }
            }

            // Перевірка, чи всі ресурси були продані
            if ($remainingQuantityToSell > 0) {
                throw new \Exception('Failed to fully sell resources from train cargo. Remaining: ' . $remainingQuantityToSell);
            }
        });

        return back()->with('success', 'Successfully sold ' . $validated['quantity'] . ' ' . $resource->name . ' for $' . number_format($totalSellPrice, 2) . '.');
    }
}
