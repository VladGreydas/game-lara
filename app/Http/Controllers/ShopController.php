<?php

namespace App\Http\Controllers;

use App\Factories\WagonShopFactory;
use App\Models\CargoWagon;
use App\Models\City;
use App\Models\Locomotive;
use App\Models\Player;
use App\Models\Wagon;
use App\Models\Weapon;
use App\Models\WeaponWagon;
use App\Services\ShopSellService;
use App\Services\ShopSetupService;
use Database\Factories\CargoWagonFactory;
use Database\Factories\WeaponWagonFactory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('city.shop.index', compact(
            'city',
            'locomotives',
            'wagons',
            'weapons',
            'weaponWagonsForMounting',
            'playerWagonsForSale',     // Передаємо вагони для продажу
            'playerWeaponsForSale'
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
}
