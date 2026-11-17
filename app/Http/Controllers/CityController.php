<?php

namespace App\Http\Controllers;

use App\Models\CityRoute;
use App\Models\Locomotive;
use App\Models\Player;
use App\Models\CityResource;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                return $player->processArrival($player);
            } else {
                // Still traveling, redirect to a travel status page or show a message
                $route = CityRoute::find($player->current_city_route_id);
                return view('city.on_route', compact('player', 'route')); // NEW: Create a travel status view
            }
        }

        // If not traveling, load city data as usual
        $city = $player->city->load(['outgoingRoutes' => function ($query) {
            $query->where('type', 'city_to_city')->with(['toCity', 'toLocation']);
        }, 'resources.resource']);

        $refuelPrice = $this->getRefuelPrice($player->train->locomotive, $city);

        return view('city.show', compact('city', 'player', 'refuelPrice'));
    }

    public function travel(Request $request, $destination): RedirectResponse
    {
        $player = $request->user()->player;

        if (!$destination instanceof CityRoute) {
            abort(404);
        }

        // Подорож між містами або містом ↔ локацією
        $fromType = $destination->type;
        $from = $destination->getFrom();
        $fromId = $destination->{'from_'.$from.'_id'};

        if (!$destination->isAvailableFrom($fromId, $fromType)) {
            abort(403, 'Маршрут недоступний.');
        }

        /** @var Locomotive $locomotive */
        $locomotive = $player->train->locomotive;

        if ($locomotive->fuel < $locomotive->getFuelCost($destination)) {
            return back()->with('error', 'Not enough fuel to start the journey.');
        }

        DB::transaction(function () use ($locomotive, $destination, $player) {
            // Consume fuel
            $locomotive->fuel -= $locomotive->getFuelCost($destination);
            $locomotive->save();

            // Get travel time
            $travelTime = $locomotive->getTravelTime($destination);

            $player->current_city_route_id = $destination->id;
            $player->travel_starts_at = now();
            $player->travel_finishes_at = now()->addHours($travelTime);

            // Оновлюємо локацію/місто відразу під час подорожі
            if ($destination->isCityToLocation()) {
                $player->current_location_id = $destination->toLocation->id;
                $player->city_id = null;
            } elseif ($destination->isLocationToCity()) {
                $player->city_id = $destination->toCity->id;
                $player->current_location_id = null;
            } else {
                // city_to_city або інші випадки
                $player->city_id = $destination->toCity->id;
                $player->current_location_id = null;
            }

            $player->save();
        });

        return redirect()->back()->with('success', 'Подорож розпочалася.');
    }

    public function refuel(Request $request)
    {
        /** @var Player $player */
        $player = auth()->user()->player;
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

        $refuelPrice = $this->getRefuelPrice($locomotive, $player->city->loadMissing(['resources.resource']))
            * ($locomotive->max_fuel - $locomotive->fuel);

        if ($player->money < $refuelPrice) {
            return back()->with('error', 'Not enough money to refuel.');
        }

        // Update player and locomotive
        $player->decrement('money', $refuelPrice);
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

    /**
     * Get the buy price for the fuel type required by the locomotive.
     */
    private function getRefuelPrice(Locomotive $locomotive, $city): float
    {
        $fuelType = $locomotive->getFuelType();

        // Try to find the fuel resource in the city's resources
        foreach ($city->resources as $resource) {
            if ($resource->resource->slug === $fuelType) {
                return $resource->getCurrentBuyPrice();
            }
        }

        // Fallback to base price
        $refuelResource = Resource::where('slug', $fuelType)->first();
        return $refuelResource ? $refuelResource->base_price : 0;
    }
}
