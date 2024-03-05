<?php

namespace App\Http\Controllers;

use App\Models\Train;
use App\Models\Weapon;
use App\Models\WeaponWagon;
use Illuminate\Http\Request;

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
    public function upgrade(Request $request, Weapon $weapon)
    {
        $status = [];
        if ($weapon->lvlUp()) {
            $status['status'] = 'success';
            $status['message'] = 'Successfully upgraded '.$weapon->name;
        } else {
            $status['status'] = 'failed';
            $status['message'] = 'Not enough money to upgrade';
        }
        return redirect(route('town.index'))->with('status', $status);
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
    public function destroy(Weapon $weapon)
    {
        //
    }
}
