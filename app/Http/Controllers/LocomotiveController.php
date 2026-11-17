<?php

namespace App\Http\Controllers;

use App\Models\Locomotive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocomotiveController extends Controller
{
    /**
     * Upgrade the specified locomotive.
     */
    public function upgrade(Locomotive $locomotive): RedirectResponse
    {
        $this->authorize('update', $locomotive);

        try {
            if (!$locomotive->lvlUp()) {
                return back()->with('error', 'Not enough money or max level reached.');
            }
            return back()->with('success', 'Locomotive upgraded successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Unauthorized or error: ' . $e->getMessage());
        }
    }

    /**
     * Rename a locomotive.
     */
    public function rename(Request $request, Locomotive $locomotive): RedirectResponse
    {
        $this->authorize('update', $locomotive);

        $name = (string)$request['new_name'];
        $locomotive->update(['name' => $name]);

        return redirect(route('player.index'));
    }

    /**
     * Repair the locomotive.
     */
    public function repair(Locomotive $locomotive): RedirectResponse
    {
        $this->authorize('update', $locomotive);

        if (! $locomotive->repair()) {
            return back()->with('error', 'Cannot repair Locomotive (not damaged or insufficient funds)');
        }

        return back()->with('success', 'Locomotive repaired!');
    }
}
