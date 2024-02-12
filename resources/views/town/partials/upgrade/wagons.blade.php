<?php
    $type = str_replace('App\\Models\\', '', $wagon->wagonable_type);
    $spec = $wagon->wagonable;
?>
<div class="p-6 flex-col">
    <p class="mt-4 text-lg text-gray-900">Name:             {{ $wagon->name }}</p>
    <p class="mt-4 text-lg text-gray-900">Level:            {{ $wagon->lvl }}(+1)</p>
    @if($spec instanceof \App\Models\CargoWagon)
        <p class="mt-4 text-lg text-gray-900">Weight:           {{ $wagon->weight }} (+{{ 50 * ($wagon->lvl+1) }}) t</p>
        <p class="mt-4 text-lg text-gray-900">Armor:            {{ $wagon->max_armor }} (+{{ 100 * ($wagon->lvl+1) }})</p>
        <p class="mt-4 text-lg text-gray-900">Capacity:            {{ $spec->capacity }} (+{{ 5 * ($wagon->lvl+1) }})</p>
    @elseif($spec instanceof \App\Models\WeaponWagon)
        <p class="mt-4 text-lg text-gray-900">Weight:           {{ $wagon->weight }} (+{{ 100 * ($wagon->lvl+1) }}) t</p>
        <p class="mt-4 text-lg text-gray-900">Armor:            {{ $wagon->max_armor }} (+{{ 150 * ($wagon->lvl+1) }})</p>
    @endif
    <p class="mt-4 text-lg text-gray-900">Upgrade Cost:     {{ $wagon->upgrade_cost }}</p>

    <form method="POST" action="{{ route('wagon.upgrade', $wagon) }}">
        @csrf
        @method('PATCH')
        <div class="mt-4 space-x-2">
            <x-primary-button>{{ __('Upgrade') }}</x-primary-button>
        </div>
    </form>
</div>
