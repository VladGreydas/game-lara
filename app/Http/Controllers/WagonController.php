<?php

namespace App\Http\Controllers;

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
        $status = [];
        if ($wagon->lvlUp()) {
            $status['status'] = 'success';
            $status['message'] = 'Successfully upgraded '.$wagon->name;
        } else {
            $status['status'] = 'failed';
            $status['message'] = 'Not enough money to upgrade';
        }
        return redirect(route('town.index'))->with('status', $status);
    }

    /**
     * Update the specified resource in storage.
     */
    public function barter(Request $request, Wagon $wagon)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wagon $wagon)
    {
        //
    }
}
