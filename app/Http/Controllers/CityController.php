<?php

namespace App\Http\Controllers;

use App\Models\CityRoute;
use App\Models\Locomotive;
use App\Models\Player;
use App\Models\CityResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon; // Додаємо Carbon для роботи з часом

class CityController extends Controller
{
    public function show()
    {
        $player = Auth::user()->player;

        // NEW: Check if the player is currently traveling
        if ($player->isTraveling() || $player->hasArrived()) {
            // Check if travel has finished
            if ($player->travel_finishes_at->isPast()) {
                // Travel has finished, process arrival
                return $this->processArrival($player);
            } else {
                // Still traveling, redirect to a travel status page or show a message
                $route = CityRoute::find($player->current_city_route_id);
                return view('city.on_route', compact('player', 'route')); // NEW: Create a travel status view
            }
        }

        // If not traveling, load city data as usual
        $city = $player->city->load('outgoingRoutes.toCity');

        return view('city.show', compact('city', 'player'));
    }

    public function travel(CityRoute $route)
    {
        /** @var Player $player */
        $player = Auth::user()->player;

        // NEW: Prevent travel if already traveling
        if ($player->isTraveling()) {
            return back()->with('error', 'You are already traveling!');
        }

        // Перевірка пального, валідності маршруту тощо
        if (!$route->isAvailableFrom($player->city_id)) {
            abort(403, 'Invalid route');
        }

        /** @var Locomotive $locomotive */
        $locomotive = $player->train->locomotive;

        if ($locomotive->fuel < $route->fuel_cost) {
            return back()->with('error', 'Not enough fuel to start the journey.');
        }

        // Consume fuel
        $locomotive->fuel -= $route->fuel_cost;
        $locomotive->save();

        // Process travel time
        $travelTime = $route->travel_time;
        $speedMultiplier = $player->train->getSpeedMultiplierAttribute();
        $finalTravelTime = $travelTime / $speedMultiplier;

        // Initiate travel
        $player->city_id = null; // Player is no longer "in" a city
        $player->current_city_route_id = $route->id; // Mark the current route
        $player->travel_starts_at = Carbon::now();
        $player->travel_finishes_at = Carbon::now()->addHours($finalTravelTime); // Calculate arrival time
        $player->save();

        return redirect()->route('player.index')->with('success', 'You have started your journey to ' . $route->toCity->name);
    }

    public function refuel(Request $request)
    {
        /** @var Player $player */
        $player = auth()->user()->player;
        /** @var Locomotive $locomotive */
        $locomotive = $player->train->locomotive;

        // Prevent refuel if player is traveling
        if ($player->isTraveling()) {
            return back()->with('error', 'Cannot refuel while traveling!');
        }

        if (!$locomotive) {
            return back()->with('error', 'You don’t have a locomotive.');
        }

        $missingFuel = $locomotive->max_fuel - $locomotive->fuel;

        if ($missingFuel === 0) {
            return back()->with('error', 'Fuel tank is already full.');
        }

        // Assume fuel price is fixed for now, e.g., 2 money per unit
        $cost = $missingFuel * 2;

        if ($player->money < $cost) {
            return back()->with('error', 'Not enough money to refuel.');
        }

        // Update player and locomotive
        $player->decrement('money', $cost);
        $locomotive->update(['fuel' => $locomotive->max_fuel]);

        return back()->with('success', 'Locomotive refueled!');
    }

    public function upgradeCity(Request $request)
    {
        $player = Auth::user()->player;
        $city = $player->city;

        $this->authorize('upgradeForUser', $city);

        if (!$city->upgrade()) {
            return back()->with('error', 'Not enough money or city is max level.');
        }

        return back()->with('success', 'City upgraded to level ' . $city->level . '!');
    }

    public function upgradeResource(Request $request, CityResource $cityResource)
    {
        $player = Auth::user()->player;

        if ($cityResource->city_id !== $player->city_id) {
            abort(403, 'You can only upgrade resources in your current city.');
        }

        $this->authorize('manageResources', $cityResource->city);

        if (!$cityResource->upgrade()) {
            return back()->with('error', 'Not enough money or resource is max level.');
        }

        return back()->with('success', 'Resource upgraded to level ' . $cityResource->level . '!');
    }
}
