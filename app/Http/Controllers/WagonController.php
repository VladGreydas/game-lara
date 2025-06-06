<?php

namespace App\Http\Controllers;

use App\Models\CargoWagon;
use App\Models\Train;
use App\Models\Wagon;
use App\Models\WeaponWagon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class WagonController extends Controller
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
     * Update the specified resource in storage.
     */
    public function upgrade(Wagon $wagon)
    {
        try {
            if (!$wagon->lvlUp()) {
                return back()->with('error', 'Not enough money or max level reached.');
            }

            return back()->with('success', 'Wagon upgraded!');
        } catch (\Exception $e) {
            return back()->with('error', 'Unauthorized: ' . $e->getMessage());
        }
    }

    /**
     * Create (purchase) the wagon in storage.
     */
    public function purchase(Request $request, Train $train)
    {
        $new_wagon = (array)json_decode($request['wagon']);
        $response = $train->addWagon($new_wagon);
        return redirect(route('town.index'))->with('status', $response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function sell(Request $request, Wagon $wagon)
    {
        $player = $wagon->train->player;
        $wagonName = $wagon->name;
        $money = 0;

        switch ($wagon->getType()) {
            case 'cargo': {
                //TODO: Transfer resources or sell them
                $money = $wagon->price / 2;
                break;
            }
            case 'weapon': {
                $money = $wagon->price / 2;
                //TODO: Keep weapons as items into Cargo Wagons (if available)
                foreach ($wagon->wagonable->weapons as $weapon) {
                    $money += $weapon->price / 2;
                }
                break;
            }
        }

        $player->addMoney($money);

        $wagon->destroyRelatives();
        $wagon->delete();

        $response['status'] = 'success';
        $response['message'] = 'Successfully sold '.$wagonName;

        return redirect(route('town.index'))->with('status', $response);
    }

    public function rename(Request $request, Wagon $wagon)
    {
        $name = (string)$request['new_name'];
        $wagon->update(['name' => $name]);

        return redirect(route('player.index'));
    }
}
