<?php

namespace App\Http\Controllers;

use App\Models\Locomotive;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class LocomotiveController extends Controller
{
    /**
     * Update (levelup) the locomotive in storage.
     */
    public function upgrade(Request $request, Locomotive $locomotive): Application|Redirector|RedirectResponse
    {
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
     * Update (purchase) the locomotive in storage.
     */
    public function purchase(Request $request, Locomotive $locomotive): Application|Redirector|RedirectResponse
    {
        $new_locomotive = (array)json_decode($request['locom']);
        $new_locomotive = Locomotive::factory()->make($new_locomotive);
        $status = [];
        if ($locomotive->purchase($new_locomotive)) {
            $status['status'] = 'success';
            $status['message'] = 'Successfully bought '.$new_locomotive->name;
        } else {
            $status['status'] = 'failed';
            $status['message'] = 'Not enough money to buy';
        }
        return redirect(route('town.index'))->with('status', $status);
    }

    /**
     * Rename a locomotive.
     *
     * @param Request $request
     * @param Locomotive $locomotive
     * @return Application|Redirector|RedirectResponse
     */
    public function rename(Request $request, Locomotive $locomotive): Application|Redirector|RedirectResponse
    {
        $name = (string)$request['new_name'];
        $locomotive->update(['name' => $name]);

        return redirect(route('player.index'));
    }

    /**
     * Repair the locomotive.
     *
     * @param Locomotive $locomotive
     * @return RedirectResponse
     */
    public function repair(Locomotive $locomotive)
    {
        if (! $locomotive->repair()) {
            return back()->with('error', 'Cannot repair Locomotive (not damaged or insufficient funds)');
        }

        return back()->with('success', 'Locomotive repaired!');
    }
}
