<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Services\MapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MapController extends Controller
{
    public function index(Request $request, MapService $mapService)
    {
        /** @var Player|null $player */
        $player = Auth::user()?->player;

        if ($player) {
            $player->load(['city', 'currentLocation', 'train.locomotive']);
        }

        $mapData = $mapService->generateMapData($player);

        return view('map.index', array_merge($mapData, ['player' => $player]));
    }
}
