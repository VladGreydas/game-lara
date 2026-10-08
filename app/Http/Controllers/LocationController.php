<?php

namespace App\Http\Controllers;

use App\Models\CityRoute;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function show(Location $location)
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

        $location->location_resource->produce();

        return view('locations.show', compact('location', 'player'));
    }

    public function collect(Request $request, Location $location): RedirectResponse
    {
        $player = $request->user()->player;

        if ($player->isTraveling() || $player->hasArrived()) {
            return redirect()->back()->with('error', __('error.location.collect_travelling'));
        }

        $locationResource = $location->location_resource;

        if (!$locationResource) {
            return redirect()->back()->with('error', 'error.location.no_resources');
        }

        if ($locationResource->collectForPlayer($player)) {
            return redirect()->back()->with('success', 'common.location.collect_success');
        } else {
            return redirect()->back()->with('error', 'error.location.collect_error');
        }
    }

    public function refuel(Request $request, Location $location): RedirectResponse
    {
        $player = $request->user()->player;

        if ($player->isTraveling() || $player->hasArrived()) {
            return redirect()->back()->with('error', __('error.location.refuel_travelling'));
        }

        if ($location->refuel($player)) {
            return redirect()->back()->with('success', 'common.location.refuel_success');
        } else {
            return redirect()->back()->with('error', 'error.location.refuel_error');
        }
    }
}
