<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    /**
     * Accept a contract offered on a city's saloon contract board.
     */
    public function accept(City $city, Contract $contract): RedirectResponse
    {
        if (!$city->hasSaloon()) {
            abort(403, 'Saloon is not built in this city.');
        }

        if ($contract->city_id !== $city->id) {
            abort(404);
        }

        $player = Auth::user()->player;

        if ($player->isTraveling()) {
            return back()->with('error', __('error.contract.accept_travelling'));
        }

        if ($player->city_id !== $city->id) {
            return back()->with('error', __('error.contract.accept_wrong_city'));
        }

        if (!$contract->isAvailable()) {
            return back()->with('error', __('error.contract.not_available'));
        }

        $contract->accept($player);

        return back()->with('success', __('common.contract.accept_success'));
    }

    /**
     * Deliver an accepted contract at its destination town.
     */
    public function deliver(Contract $contract): RedirectResponse
    {
        $player = Auth::user()->player;

        if ($contract->player_id !== $player->id) {
            abort(403);
        }

        if ($player->isTraveling()) {
            return back()->with('error', __('error.contract.deliver_travelling'));
        }

        if (!$contract->canBeDeliveredBy($player)) {
            return back()->with('error', __('error.contract.deliver_error'));
        }

        $reward = $contract->reward;
        $contract->deliverBy($player);

        return back()->with('success', __('common.contract.deliver_success', ['reward' => $reward]));
    }
}
