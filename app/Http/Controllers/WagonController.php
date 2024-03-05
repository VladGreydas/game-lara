<?php

namespace App\Http\Controllers;

use App\Models\Train;
use App\Models\Wagon;
use Illuminate\Http\Request;

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
    public function upgrade(Request $request, Wagon $wagon)
    {
        $response = $wagon->lvlUp();
        return redirect(route('town.index'))->with('status', $response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function sell(Request $request, Wagon $wagon)
    {
        $wagon = Wagon::find($request['replace-wagon-id']);
        $new_wagon = (array)json_decode($request['new_wagon']);


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
    public function destroy(Wagon $wagon)
    {
        //
    }
}
