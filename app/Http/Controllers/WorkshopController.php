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

        abort_unless($city && $city->has_workshop, 403);

        return view('city.workshop.index', [
            'player' => $player,
            'wagons' => $player->train->wagons,
            'repairCost' => $this->locomotiveRepairCost($player),
        ]);
    }

    public function repairLocomotive(Request $request)
    {
        /** @var Player $player */
        $player = $request->user()->player;

        $cost = $this->locomotiveRepairCost($player);
        abort_if($player->money < $cost, 403);

        $player->money -= $cost;
        $player->train->locomotive->armor = $player->train->locomotive->max_armor;
        $player->train->locomotive->save();
        $player->save();

        return back()->with('status', 'Locomotive repaired.');
    }

    public function upgradeWagon(Request $request, Wagon $wagon)
    {
        /** @var Player $player */
        $player = $request->user()->player;

        abort_if($wagon->train->player_id !== $player->id, 403);

        $cost = $wagon->upgrade_cost;
        abort_if($player->money < $cost, 403);

        $player->money -= $cost;
        $wagon->lvl++;
        $wagon->save();
        $player->save();

        return back()->with('status', 'Wagon upgraded.');
    }

    public function upgradeWeapon(Request $request, Weapon $weapon)
    {
        $player = $request->user()->player;

        abort_if($weapon->player_id !== $player->id, 403);

        $cost = $weapon->upgradeCost();
        abort_if($player->money < $cost, 403);

        $player->money -= $cost;
        $weapon->lvl++;
        $weapon->damage += 5; // умовно
        $weapon->save();
        $player->save();

        return back()->with('status', 'Weapon upgraded.');
    }

    private function locomotiveRepairCost(Player $player): int
    {
        $missingHp = $player->train->locomotive->max_armor - $player->train->locomotive->hp;
        return ceil($missingHp * 0.5); // 0.5 coins per HP
    }
}
