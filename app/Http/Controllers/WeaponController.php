<?php

namespace App\Http\Controllers;

use App\Models\Train;
use App\Models\Weapon;
use App\Models\WeaponWagon;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class WeaponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Weapon $weapon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Weapon $weapon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function upgrade(Weapon $weapon)
    {
        try {
            if (!$weapon->lvlUp()) {
                return back()->with('error', 'Not enough money or max level reached.');
            }

            return back()->with('success', 'Weapon upgraded!');
        } catch (\Exception $e) {
            return back()->with('error', 'Unauthorized: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function purchase(Request $request)
    {
        if (!$request['wagon']) {
            $response['status'] = 'failed';
            $response['message'] = 'Choose wagon before weapon purchase';
        } else {
            $weapon_wagon = WeaponWagon::find($request['wagon']);
            $weapon = (array)json_decode($request['weapon']);
            $weapon = Weapon::factory()->make($weapon);
            $response = $weapon_wagon->purchaseWeapon($weapon);
        }
        return redirect(route('town.index'))->with('status', $response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function sell(Weapon $weapon)
    {
        $player = $weapon->weapon_wagon->wagon->train->player;
        $weaponName = $weapon->name;

        $weapon->weapon_wagon->addSlot();
        $player->addMoney($weapon->price / 2);

        $weapon->delete();

        $response['status'] = 'success';
        $response['message'] = 'Successfully sold '.$weaponName;

        return redirect(route('town.index'))->with('status', $response);
    }

    /**
     * Weapon rename.
     *
     * @param Request $request
     * @param Weapon $weapon
     * @return Application|Redirector|RedirectResponse
     */
    public function rename(Request $request, Weapon $weapon): Application|Redirector|RedirectResponse
    {
        $name = (string)$request['new_name'];
        $weapon->update(['name' => $name]);

        return redirect(route('player.index'));
    }
}
