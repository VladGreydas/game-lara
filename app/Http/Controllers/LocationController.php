<?php

namespace App\Http\Controllers;

use App\Models\CityRoute;
use App\Models\Location;
use App\Services\ProcessingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function show(Location $location, ProcessingService $processingService)
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

        $location->loadMissing(['location_resource.resource', 'location_resource.inputResource', 'location_resource.inputResource2']);

        // Use ProcessingService for T1 locations, standard produce for T0
        $locationResource = $location->location_resource;
        if ($locationResource) {
            if ($locationResource->input_resource_id) {
                $processingService->produce($locationResource);
            } else {
                $locationResource->produce();
            }
        }

        // Get processing status for T1 locations
        $processingStatus = $locationResource
            ? $processingService->getProcessingStatus($locationResource)
            : null;

        // Get production status for T0 locations (standard production with time)
        $productionStatus = null;
        if ($locationResource && !$locationResource->input_resource_id) {
            $productionStatus = [
                'current_amount' => $locationResource->current_amount,
                'max_capacity' => $locationResource->max_capacity,
                'production_rate_per_hour' => $locationResource->production_rate_per_hour,
                'progress_percent' => min(100, (int) ($locationResource->current_amount / $locationResource->max_capacity * 100)),
                'estimated_time_minutes' => 0,
            ];

            // Calculate estimated time to full capacity
            $remaining = $locationResource->max_capacity - $locationResource->current_amount;
            if ($remaining > 0 && $locationResource->production_rate_per_hour > 0) {
                $hoursToFull = $remaining / $locationResource->production_rate_per_hour;
                $productionStatus['estimated_time_minutes'] = (int) ceil($hoursToFull * 60);
            }
        }

        // Get available T0 input resources in player's wagons for T1 locations
        $availableInputs = [];
        if ($locationResource && $locationResource->input_resource_id) {
            $availableAmount = $processingService->getInputResourceInWagons(
                $player,
                $locationResource->input_resource_id
            );
            $availableInputs = [
                'resource_id' => $locationResource->input_resource_id,
                'resource_name' => $locationResource->inputResource->name ?? null,
                'available_amount' => $availableAmount,
            ];

            // If location has a second input resource, get its available amount too
            if ($locationResource->input_resource_2_id) {
                $availableAmount2 = $processingService->getInputResourceInWagons(
                    $player,
                    $locationResource->input_resource_2_id
                );
                $availableInputs['second'] = [
                    'resource_id' => $locationResource->input_resource_2_id,
                    'resource_name' => $locationResource->inputResource2->name ?? null,
                    'available_amount' => $availableAmount2,
                ];
            }
        }

        return view('locations.show', compact('location', 'player', 'processingStatus', 'productionStatus', 'availableInputs'));
    }

    public function collect(Request $request, Location $location, ProcessingService $processingService): RedirectResponse
    {
        $player = $request->user()->player;

        if ($player->isTraveling() || $player->hasArrived()) {
            return redirect()->back()->with('error', __('error.location.collect_travelling'));
        }

        $locationResource = $location->location_resource;

        if (!$locationResource) {
            return redirect()->back()->with('error', 'error.location.no_resources');
        }

        // Use ProcessingService for T1 locations to update processing queue
        if ($locationResource->input_resource_id) {
            $processingService->produce($locationResource);
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

    public function unload(Request $request, Location $location, ProcessingService $processingService): RedirectResponse
    {
        $player = $request->user()->player;

        if ($player->isTraveling() || $player->hasArrived()) {
            return redirect()->back()->with('error', __('error.location.unload_travelling'));
        }

        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $result = $processingService->unloadResource($player, $location, $validated['amount']);

        if ($result['success']) {
            return redirect()->back()->with('success', __('location.success.unloaded', ['amount' => $result['output_amount']]));
        } else {
            return redirect()->back()->with('error', __($result['message']));
        }
    }
}
