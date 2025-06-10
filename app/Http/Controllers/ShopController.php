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
use Database\Factories\CargoWagonFactory;
use Database\Factories\WeaponWagonFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShopController extends Controller
{

    public function index(Request $request)
    {
        /** @var Player $player */
        $player = $request->user()->player;
        /** @var City $city */
        $city = $player->city;
        $factory = Locomotive::factory();

        $locomotives = $factory->makeMultipleShopLocomotives();

        // Присвоюємо UUID кожному локомотиву та зберігаємо в сесію
        $locomotives->each(function ($locomotive) {
            $uuid = (string) Str::uuid();
            $locomotive->shop_uuid = $uuid;
            session()->put("shop.locomotives.$uuid", $locomotive);
        });

        $wagons = WagonShopFactory::makeShopWagons();
        $wagons->each(function ($wagon) {
            $uuid = (string) Str::uuid();
            $wagon->shop_uuid = $uuid;
            session()->put("shop.wagons.$uuid", $wagon);
        });

        $weapons = Weapon::factory()->generateShopWeapons();
        $weapons->each(function ($weapon) {
            $uuid = $weapon->shop_uuid;
            session()->put("shop.weapons.$uuid",$weapon);
        });

        $weaponWagons = $player->train->checkAvailableWeaponWagons();
        $weaponWagons = collect($weaponWagons);

        return view('city.shop.index', compact('city', 'locomotives', 'wagons', 'weaponWagons', 'weapons'));
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
}
