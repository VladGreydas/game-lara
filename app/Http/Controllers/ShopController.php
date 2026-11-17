<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Player;
use App\Models\Resource;
use App\Models\Wagon;
use App\Models\Weapon;
use App\Models\WeaponWagon;
use App\Services\ShopBuyService;
use App\Services\ShopSellService;
use App\Services\ShopSetupService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ShopController extends Controller
{
    public function __construct(
        protected ShopSetupService $shopSetupService,
        protected ShopSellService $shopSellService,
        protected ShopBuyService $shopBuyService
    ) {
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

        // Eager loading для потяга гравця з усіма вагонами та ресурсами
        $player->loadMissing([
            'train.locomotive',
            'train.wagons.cargo_wagon.resources.resource',
            'train.wagons.weapon_wagon.weapons',
        ]);

        $weaponWagonsForMounting = $player->train->checkAvailableWeaponWagons();
        $weaponWagonsForMounting = collect($weaponWagonsForMounting);

        // Eager loading замість whereHas — один запит замість двох
        $playerWagonsForSale = \App\Models\Wagon::where('train_id', $player->train->id)->get();
        $playerWeaponsForSale = \App\Models\Weapon::whereHas('weapon_wagon.wagon.train', function ($query) use ($player) {
            $query->where('player_id', $player->id);
        })->with('weapon_wagon')->get();

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
                    $totalCargoCapacity += $wagon->cargo_wagon->capacity;
                    $currentCargoCapacity += $wagon->cargo_wagon->getCurrentCapacity();

                    // Додаємо ресурси цього вантажного вагона до загальної колекції ресурсів гравця
                    $playerCargoWagonResources = $playerCargoWagonResources->concat($wagon->cargo_wagon->resources);
                }
            }
        }
        $availableCargoSpace = $totalCargoCapacity - $currentCargoCapacity;

        $groupedPlayerCargoWagonResources = $playerCargoWagonResources->groupBy('resource_id')->map(function ($items) {
            $firstItem = $items->first();
            // Повертаємо об'єкт, схожий на CargoWagonResource, але з агрегованою кількістю
            return (object)[
                'resource_id' => $firstItem->resource_id,
                'resource' => $firstItem->resource, // Залишаємо об'єкт Resource
                'quantity' => $items->sum('quantity'), // Сумуємо кількості
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
            'cityResources',
            'playerCargoWagonResources',
            'groupedPlayerCargoWagonResources',
            'availableCargoSpace',
            'totalCargoCapacity'
        ));
    }

    public function buyLocomotive(string $uuid)
    {
        $player = Auth::user()->player;

        try {
            $this->shopBuyService->buyLocomotive($player, $uuid);
            return back()->with('success', 'Locomotive successfully purchased and installed!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function buyWagon(string $uuid)
    {
        $player = Auth::user()->player;

        try {
            $this->shopBuyService->buyWagon($player, $uuid);
            return back()->with('success', 'Wagon purchased successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function sellWagon(Request $request, Wagon $wagon) // <-- Тепер приймає об'єкт Wagon
    {
        /** @var Player $player */
        $player = Auth::user()->player;

        if ($wagon->train->player->id !== $player->id) {
            throw new AuthorizationException('You do not own this wagon.');
        }

        try {
            $destinationWagonId = $request->input('destination_wagon_id');

            $this->shopSellService->sellWagon($player, $wagon, $destinationWagonId);

            return back()->with('success', 'Wagon sold successfully!');

        } catch (ValidationException $e) {
            return back()->with('error', 'Validation error: ' . $e->getMessage())->withErrors($e->errors());
        } catch (AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error("Sell wagon error: " . $e->getMessage(), ['player_id' => $player->id, 'wagon_id' => $wagon->id]);
            return back()->with('error', 'An unexpected error occurred during wagon sale. ' . $e->getMessage());
        }
    }

    public function buyWeapon(Request $request, string $shop_uuid)
    {
        $player = Auth::user()->player;

        $validated = $request->validate([
            'weapon_wagon_id' => ['required', 'exists:weapon_wagons,id'],
        ]);

        try {
            $weapon = $this->shopBuyService->buyWeapon($player, $shop_uuid, $validated['weapon_wagon_id']);
            return back()->with('success', "Weapon {$weapon->name} successfully mounted on wagon #{$weapon->weapon_wagon_id}.");
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
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
            $this->shopSellService->sellWeapon($player, $weapon);

            return back()->with('success', 'Weapon sold successfully!');

        } catch (AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error("Sell weapon error: " . $e->getMessage(), ['player_id' => $player->id, 'weapon_id' => $weapon->id]);
            return back()->with('error', 'An unexpected error occurred during weapon sale. ' . $e->getMessage());
        }
    }

    public function buyResource(Request $request, Resource $resource)
    {
        $player = Auth::user()->player;

        $validated = $request->validate([
            'city_resource_id' => ['required', 'integer', 'exists:city_resources,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->shopBuyService->buyResource(
                $player,
                $validated['city_resource_id'],
                $validated['quantity'],
                $resource->slug
            );
            return back()->with('success', 'Successfully purchased ' . $validated['quantity'] . ' ' . $resource->name . '.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function sellResource(Request $request, Resource $resource)
    {
        $player = Auth::user()->player;

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $totalSellPrice = $this->shopBuyService->sellResource($player, $resource, $validated['quantity']);
            return back()->with('success', 'Successfully sold ' . $validated['quantity'] . ' ' . $resource->name . ' for $' . number_format($totalSellPrice, 2) . '.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
