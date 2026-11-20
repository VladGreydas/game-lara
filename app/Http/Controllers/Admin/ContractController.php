<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Contract;
use App\Models\Resource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 50, 100]) ? $perPage : 10;

        $contracts = Contract::with(['city', 'resource', 'destinationCity'])
            ->latest()
            ->paginate($perPage);
        return view('admin.contracts.index', compact('contracts'));
    }

    public function create(): View
    {
        return view('admin.contracts.create', [
            'cities' => City::where('has_saloon', true)->get(),
            'resources' => Resource::all(),
            'destinationCities' => City::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'resource_id' => 'required|exists:resources,id',
            'destination_city_id' => 'required|exists:cities,id',
            'quantity' => 'required|integer|min:1',
            'reward' => 'required|integer|min:1',
        ]);

        Contract::create([
            'city_id' => $request->city_id,
            'resource_id' => $request->resource_id,
            'destination_city_id' => $request->destination_city_id,
            'quantity' => $request->quantity,
            'reward' => $request->reward,
            'status' => Contract::STATUS_AVAILABLE,
        ]);

        return redirect()->route('admin.contracts.index')
            ->with('success', __('admin.contract_created'));
    }

    public function destroy(Contract $contract): RedirectResponse
    {
        $contract->delete();
        return redirect()->route('admin.contracts.index')
            ->with('success', __('admin.contract_deleted'));
    }
}
