<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaloonController extends Controller
{
    public function show(City $city)
    {
        if (!$city->hasSaloon()) {
            abort(403, 'Saloon is not built in this city.');
        }

        Contract::generateForCity($city);

        $contracts = Contract::where('city_id', $city->id)
            ->where('status', Contract::STATUS_AVAILABLE)
            ->with(['resource', 'destinationCity'])
            ->get();

        $player = Auth::user()->player;

        return view('city.saloon.show', compact('city', 'contracts', 'player'));
    }
}
