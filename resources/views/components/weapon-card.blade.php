<?php
use App\Models\Weapon;
/** @var Weapon $weapon */
?>
<div class="border rounded-lg shadow p-4 bg-white">
    <div class="flex flex-row pb-1">
        <h4 class="text-md font-bold text-gray-900 m-1">{{ $weapon->name }}</h4>
        @if($rename)
            <x-rename type="weapon" id="{{$weapon->id}}" name="{{$weapon->name}}"/>
        @endif
    </div>
    <p class="text-sm text-gray-700"><span class="font-semibold">DMG:</span> {{ $weapon->damage }}</p>
    <p class="text-sm text-gray-700"><span class="font-semibold">Type:</span> {{ $weapon->type }}</p>
    <p class="text-sm text-gray-700"><span class="font-semibold">Level:</span> {{ $weapon->lvl }}</p>
    @if($upgrade)
        <p class="text-sm text-gray-700"><span class="font-semibold">Upgrade cost:</span> {{ $weapon->upgrade_cost }}
        </p>
        <form action="{{ route('weapon.upgrade', $weapon->id) }}" method="POST">
            @csrf
            <x-primary-button>Upgrade {{ $weapon->name }}</x-primary-button>
        </form>
    @endif
</div>
