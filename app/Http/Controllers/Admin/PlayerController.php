<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Player::with(['user', 'city', 'train.locomotive']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 50, 100]) ? $perPage : 10;

        $players = $query->latest()->paginate($perPage);

        return view('admin.players.index', compact('players'));
    }

    public function edit(Player $player): View
    {
        return view('admin.players.edit', [
            'player' => $player->load(['user', 'city', 'train.locomotive']),
            'cities' => City::all(),
        ]);
    }

    public function update(Request $request, Player $player): RedirectResponse
    {
        $request->validate([
            'money' => 'required|integer|min:0',
            'exp' => 'required|integer|min:0',
            'lvl' => 'required|integer|min:1',
            'stamina' => 'required|integer|min:0',
            'city_id' => 'required|exists:cities,id',
        ]);

        $player->update([
            'money' => $request->money,
            'exp' => $request->exp,
            'lvl' => $request->lvl,
            'stamina' => $request->stamina,
            'max_stamina' => 100 + ($request->lvl - 1) * 5,
            'max_exp' => (int) (100 * pow($request->lvl, 1.3)),
            'city_id' => $request->city_id,
        ]);

        return redirect()->route('admin.players.edit', $player)
            ->with('success', __('admin.player_updated'));
    }
}
