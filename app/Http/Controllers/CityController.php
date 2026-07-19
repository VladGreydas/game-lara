<?php

namespace App\Http\Controllers;

use App\Models\CityRoute;
use App\Models\Location;
use App\Models\Locomotive;
use App\Models\Player;
use App\Models\CityResource;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

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
        $city = $player->city->load(['outgoingRoutes' => function ($query) {
            $query->where('type', 'city_to_city')->with(['toCity', 'toLocation']);
        }]);

        return view('city.show', compact('city', 'player'));
    }

    public function travel(Request $request, $destination): RedirectResponse
    {
        $player = $request->user()->player;

        if ($destination instanceof CityRoute) {
            // Подорож між містами або містом ↔ локацією
            $fromType = $player->current_location_id ? 'location' : 'city';
            $fromId = $player->current_location_id ?? $player->city_id;

            if (!$destination->isAvailableFrom($fromId, $fromType)) {
                abort(403, 'Маршрут недоступний.');
            }

            $player->current_city_route_id = $destination->id;
            $player->travel_starts_at = now();
            $player->travel_finishes_at = now()->addMinutes($destination->travel_time);
            $player->current_location_id = null; // Якщо подорож починається з локації — гравець залишає її
        } elseif ($destination instanceof Location) {
            // Подорож до локації
            $player->current_location_id = $destination->id;
            $player->current_city_route_id = null;
            $player->travel_starts_at = now();
            $player->travel_finishes_at = now()->addMinutes($destination->travel_time);
        }

        $player->save();
        return redirect()->back()->with('success', 'Подорож розпочалася.');
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
