<?php

namespace App\Http\Controllers;

use App\Models\Weapon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WeaponController extends Controller
{
    /**
     * Upgrade the specified weapon.
     */
    public function upgrade(Weapon $weapon): RedirectResponse
    {
        $this->authorize('update', $weapon);

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
     * Rename a weapon.
     */
    public function rename(Request $request, Weapon $weapon): RedirectResponse
    {
        $this->authorize('update', $weapon);

        $name = (string)$request['new_name'];
        $weapon->update(['name' => $name]);

        return redirect(route('player.index'));
    }
}
