<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('player.index', [
            'player' => Player::with('user')->where('user_id', Auth::id())->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'max_exp' => 100,
            'lvl' => 1,
            'current_town_id' => 1
        ]);
        $validated = $request->validate([
            'nickname' => 'required|string|max:255',
            'max_exp' => 'int',
            'lvl' => 'int',
            'current_town_id' => 'int'
        ]);

        $request->user()->player()->create($validated);

        $player = (Player::with('user')->where('user_id', Auth::id())->get())[0];

        $player->createTrain();

        return redirect(route('player.index'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Player $player): View
    {
        $this->authorize('update', $player);

        return view('player.edit', [
            'player' => $player
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Player $player): RedirectResponse
    {
        $this->authorize('update', $player);

        $validated = $request->validate([
            'nickname' => 'string|max:255',
            'lvl' => 'int',
            'money' => 'int',
            'exp' => 'int',
            'max_exp' => 'int'
        ]);

        $player->update($validated);

        return redirect(route('player.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Player $player): RedirectResponse
    {
        $this->authorize('delete', $player);

        $player->delete();

        return redirect(route('player.index'));
    }
}
