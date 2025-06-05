<?php

namespace App\Http\Controllers;

use App\Models\CityRoute;
use App\Models\Locomotive;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller
{
    public function show()
    {
        $player = Auth::user()->player;
        $city = $player->city->load('outgoingRoutes.toCity');

        return view('city.show', compact('city', 'player'));
    }

    public function travel(CityRoute $route)
    {
        $player = Auth::user()->player;

        // Перевірка пального, валідності маршруту тощо
        if ($route->from_city_id !== $player->city_id) {
            abort(403, 'Invalid route');
        }

        $locomotive = $player->train->locomotive;

        if ($locomotive->fuel < $route->fuel_cost) {
            return back()->with('error', 'Not enough fuel.');
        }

        // Переміщення
        $locomotive->fuel -= $route->fuel_cost;
        $locomotive->save();

        $player->city_id = $route->to_city_id;
        $player->save();

        return redirect()->route('city.show')->with('success', 'You have arrived at ' . $route->toCity->name);
    }

    public function refuel(Request $request)
    {
        /** @var Player $player */
        $player = auth()->user()->player;
        /** @var Locomotive $locomotive */
        $locomotive = $player->train->locomotive;

        if (!$locomotive) {
            return back()->with('error', 'You don’t have a locomotive.');
        }

        $missingFuel = $locomotive->max_fuel - $locomotive->fuel;

        if ($missingFuel === 0) {
            return back()->with('error', 'Fuel tank is already full.');
        }

        $cost = $missingFuel * 2;

        if ($player->money < $cost) {
            return back()->with('error', 'Not enough money to refuel.');
        }

        // Update player and locomotive
        $player->decrement('money', $cost);
        $locomotive->update(['fuel' => $locomotive->max_fuel]);

        return back()->with('success', 'Locomotive successfully refueled.');
    }
}
