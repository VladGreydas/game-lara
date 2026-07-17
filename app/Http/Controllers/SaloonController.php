<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class SaloonController extends Controller
{
    public function show(City $city)
    {
        if (!$city->hasSaloon()) {
            abort(403, 'Saloon is not built in this city.');
        }

        return view('city.saloon.show', compact('city'));
    }
}
