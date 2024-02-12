<?php $fuel = $player->train->locomotive->max_fuel - $player->train->locomotive->fuel?>
<p class="mt-4 text-lg text-gray-900">Fuel: {{ $player->train->locomotive->fuel }} / {{ $player->train->locomotive->max_fuel }}</p>
@if($fuel > 0)
        <?php $cost = ($player->train->locomotive->max_fuel - $player->train->locomotive->fuel) * 5;?>
    <p class="mt-4 text-lg text-gray-900">Refuel cost: {{ $cost }}</p>
    <form method="post" action="{{route('town.refuel')}}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="player" value="{{$player->id}}">
        <input type="hidden" name="town_id" value="{{$town->id}}">
        <input type="hidden" name="cost" value="{{$cost}}">
        <x-primary-button class="ml-1 h-10 mt-5">{{__('Refuel')}}</x-primary-button>
    </form>
@endif
