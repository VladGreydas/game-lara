<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        /** @var Player $player */
        $player = Auth::user()->player;

        if ($player) {
            // Завантажуємо всі необхідні зв'язки.
            // isTraveling() використовуватиме currentCityRoute
            // inCity() використовуватиме city
            $player->load([
                'train.locomotive',
                'train.wagons.cargo_wagon.resources.resource',
                'train.wagons.weapon_wagon.weapons',
                'city', // Завантажуємо поточне місто гравця
                'currentCityRoute.fromCity', // Для інформації про подорож
                'currentCityRoute.toCity',    // Для інформації про подорож
                'currentLocation', // Для інформації, якщо гравець у локації
            ]);
        }

        return view('player.index', compact('player'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (Auth::user()->player) {
            return redirect()->route('player.index')->with('error', 'You already have a player profile.');
        }

        $validated = $request->validate([
            'nickname' => [
                'required',
                'string',
                'max:255',
                'min:3',
                Rule::unique('players', 'nickname'),
            ],
        ]);

        $player = new Player();
        $player->user_id = Auth::id();
        $player->nickname = $validated['nickname'];
        $player->money = 1000;
        $player->exp = 0;
        $player->max_exp = 100;
        $player->lvl = 1;

        $startCity = City::first(); // Переконайтеся, що міста вже створені у сидерах
        if ($startCity) {
            $player->city_id = $startCity->id;
            // $player->current_location_id = null; // Переконайтеся, що це поле встановлюється правильно
        } else {
            return back()->withErrors(['message' => 'No starting city available. Please seed cities first.']);
        }

        $player->save();

        return redirect()->route('player.index')->with('success', 'Player "' . $player->nickname . '" created successfully!');
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

    public function levelUp(Player $player)
    {
        // Це можна винести в окремий сервіс
        if (!$player->canLevelUp()) {
            return redirect()->back()->withErrors('Not enough experience to level up.');
        }

        $this->authorize('update', $player);

        $player->lvl += 1;
        $player->exp -= $player->max_exp;
        $player->max_exp = ceil($player->max_exp * 1.75);
        $player->money += 500;
        $player->save();

        return redirect()->back()->with('success', 'Level up successful!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Player $player): RedirectResponse
    {
        $this->authorize('delete', $player);

        $player->delete();

        return redirect()->route('dashboard')->with('success', 'Player profile deleted.');
    }
}
