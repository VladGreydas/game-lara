<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 50, 100]) ? $perPage : 10;

        $cities = City::withCount('resources')->latest()->paginate($perPage);
        return view('admin.cities.index', compact('cities'));
    }

    public function edit(City $city): View
    {
        return view('admin.cities.edit', [
            'city' => $city->load(['resources.resource']),
        ]);
    }

    public function update(\Illuminate\Http\Request $request, City $city): RedirectResponse
    {
        $request->validate([
            'level' => 'required|integer|min:1|max:10',
            'has_shop' => 'boolean',
            'has_workshop' => 'boolean',
            'has_saloon' => 'boolean',
        ]);

        $city->update($request->only(['level', 'has_shop', 'has_workshop', 'has_saloon']));

        return redirect()->route('admin.cities.index')
            ->with('success', __('admin.city_updated'));
    }
}
