<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Town;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TownController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('town.index', [
            'town' => Town::whereHas('players', function (Builder $query) {
                $query->where('user_id', Auth::id());
            })->get(),
            'player' => Player::with('user')->where('user_id', Auth::id())->get()
        ]);
    }

    public function refuel()
    {
        
    }

    public function depart(Town $town)
    {
        $destinations = $town->getTravelDestinations();

        return view('town.depart', [
            'destinations' => $destinations,
            'player' => Player::with('user')->where('user_id', Auth::id())->get()
        ]);
    }

    public function travel(Request $request)
    {
        $player = Player::find($request->get('player'));
        $town_id = $request->get('town_id');
        $cost = $request->get('cost');
        if($player->travel($town_id, $cost)) {
            return redirect(route('player.index'));
        } else {
            return redirect(route('town.index'));
        }
    }
}
