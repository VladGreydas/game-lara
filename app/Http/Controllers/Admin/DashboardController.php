<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Player;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'playersCount' => Player::count(),
            'citiesCount' => City::count(),
            'locationsCount' => Location::count(),
            'contractsCount' => Contract::where('status', Contract::STATUS_AVAILABLE)->count(),
            'activeTravels' => Player::whereNotNull('travel_finishes_at')->count(),
        ]);
    }
}
