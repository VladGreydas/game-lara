<?php

namespace App\Http\Controllers;

use App\Models\Wagon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WagonController extends Controller
{
    /**
     * Upgrade the specified wagon.
     */
    public function upgrade(Wagon $wagon): RedirectResponse
    {
        $this->authorize('update', $wagon);

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
     * Rename a wagon.
     */
    public function rename(Request $request, Wagon $wagon): RedirectResponse
    {
        $this->authorize('update', $wagon);

        $name = (string)$request['new_name'];
        $wagon->update(['name' => $name]);

        return redirect(route('player.index'));
    }

    /**
     * Repair the wagon.
     */
    public function repair(Wagon $wagon): RedirectResponse
    {
        $this->authorize('update', $wagon);

        if (! $wagon->repair()) {
            return back()->with('error', 'Cannot repair Wagon');
        }

        return back()->with('success', 'Wagon repaired!');
    }
}
