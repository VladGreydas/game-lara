<?php

namespace App\Http\Controllers;

use App\Models\Locomotive;
use Illuminate\Http\Request;

class LocomotiveController extends Controller
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
    public function show(Locomotive $locomotive)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Locomotive $locomotive)
    {
        //
    }

    /**
     * Update (levelup) the locomotive in storage.
     */
    public function upgrade(Request $request, Locomotive $locomotive)
    {
        $status = '';
        if ($locomotive->lvlUp()) {
            $status = 'upgrade-successful';
        } else {
            $status = 'upgrade-failed';
        }
        return redirect(route('town.index'))->with('status', $status);
    }

    /**
     * Update (purchase) the locomotive in storage.
     */
    public function barter(Request $request, Locomotive $locomotive)
    {
        //
    }
}
