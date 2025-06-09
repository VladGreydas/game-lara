<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Locomotive;
use App\Models\Player;
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

        return view('city.shop.index', compact('city', 'locomotives'));
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
}
