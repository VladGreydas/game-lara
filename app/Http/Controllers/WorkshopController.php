<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Wagon;
use App\Models\Weapon;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    public function index(Request $request)
    {
        /** @var Player $player */
        $player = $request->user()->player;
        /** @var City $city */
        $city = $player->city;
        $train = $player->train;

        $locomotive = $train->locomotive;

        $wagons = $train->wagons;

        abort_unless($city && $city->has_workshop, 403);

        return view('city.workshop.index', compact('city', 'locomotive', 'wagons'));
    }
}
