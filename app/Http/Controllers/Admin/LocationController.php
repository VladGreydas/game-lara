<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 50, 100]) ? $perPage : 10;

        $locations = Location::with(['city', 'location_resource.resource'])->latest()->paginate($perPage);
        return view('admin.locations.index', compact('locations'));
    }

    public function edit(Location $location): View
    {
        return view('admin.locations.edit', [
            'location' => $location->load(['city', 'location_resource.resource']),
        ]);
    }

    public function update(\Illuminate\Http\Request $request, Location $location): RedirectResponse
    {
        $request->validate([
            'lvl' => 'required|integer|min:1|max:10',
        ]);

        $location->update(['lvl' => $request->lvl]);

        return redirect()->route('admin.locations.index')
            ->with('success', __('admin.location_updated'));
    }
}
