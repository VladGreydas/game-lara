<?php

namespace App\Http\Controllers;

use App\Models\CityRoute;
use App\Models\Locomotive;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon; // Додаємо Carbon для роботи з часом

class CityController extends Controller
{
    public function show()
    {
        $player = Auth::user()->player;

        // NEW: Check if the player is currently traveling
        if ($player->isTraveling()) {
            // Check if travel has finished
            if ($player->travel_finishes_at->isPast()) {
                // Travel has finished, process arrival
                return $this->processArrival($player);
            } else {
                // Still traveling, redirect to a travel status page or show a message
                $route = CityRoute::find($player->current_city_route_id);
                return view('travel.on_route', compact('player', 'route')); // NEW: Create a travel status view
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

        // Initiate travel
        $player->city_id = null; // Player is no longer "in" a city
        $player->current_city_route_id = $route->id; // Mark the current route
        $player->travel_starts_at = Carbon::now();
        $player->travel_finishes_at = Carbon::now()->addHours($route->travel_time); // Calculate arrival time
        $player->save();

        return redirect()->route('player.index')->with('success', 'You have started your journey to ' . $route->toCity->name);
    }

    // NEW: Helper method to process arrival
    private function processArrival(Player $player)
    {
        /** @var CityRoute $route */
        $route = CityRoute::find($player->current_city_route_id);

        if (!$route) {
            // Handle error: route not found, maybe player state is corrupted
            $player->current_city_route_id = null;
            $player->travel_finishes_at = null;
            $player->save();
            return redirect()->route('player.index')->with('error', 'Travel state error. Please report this.');
        }

        $player->city_id = $route->to_city_id; // Set player's city to destination
        $player->current_city_route_id = null; // Clear travel route
        $player->travel_finishes_at = null; // Clear arrival time
        $player->save();

        // Redirect to the newly arrived city
        return redirect()->route('city.show', $player->city)->with('success', 'You have arrived at ' . $route->toCity->name);
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
}
