<?php $locomotive = $player->train->locomotive;?>
<h3 class="p-5 font-semibold text-xl text-gray-800 leading-tight">
    Locomotive
</h3>
<div class="p-6 flex-col">
    <p class="mt-4 text-lg text-gray-900">Name:             {{ $locomotive->name }}</p>
    <p class="mt-4 text-lg text-gray-900">Level:            {{ $locomotive->lvl }}(+1)</p>
    <p class="mt-4 text-lg text-gray-900">Weight:           {{ $locomotive->weight }} (+{{ 150 * ($locomotive->lvl+1) }})t</p>
    <p class="mt-4 text-lg text-gray-900">Power:            {{ $locomotive->power }} (+{{ 50 * ($locomotive->lvl+1) }})khp</p>
    <p class="mt-4 text-lg text-gray-900">Armor:            {{ $locomotive->max_armor }} (+{{ 100 * ($locomotive->lvl+1) }})</p>
    <p class="mt-4 text-lg text-gray-900">Fuel:             {{ $locomotive->max_fuel }} (+{{ 5 * ($locomotive->lvl+1) }})</p>
    <p class="mt-4 text-lg text-gray-900">Upgrade Cost:     {{ $locomotive->upgrade_cost }}</p>

    <form method="POST" action="{{ route('locomotive.upgrade', $locomotive) }}">
        @csrf
        @method('PATCH')
        <div class="mt-4 space-x-2">
            <x-primary-button>{{ __('Upgrade') }}</x-primary-button>
        </div>
    </form>
</div>
