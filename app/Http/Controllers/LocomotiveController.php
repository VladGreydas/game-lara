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
        $status = [];
        if ($locomotive->lvlUp()) {
            $status['status'] = 'success';
            $status['message'] = 'Successfully upgraded '.$locomotive->name;
        } else {
            $status['status'] = 'failed';
            $status['message'] = 'Not enough money to upgrade';
        }
        return redirect(route('town.index'))->with('status', $status);
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
}
