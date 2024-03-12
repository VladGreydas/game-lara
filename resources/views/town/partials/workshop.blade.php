<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Choose what do you want to upgrade') }}
</h2>
<?php
    foreach ($player->train->wagons as $wagon) {
        if($wagon->wagonable instanceof \App\Models\WeaponWagon && $wagon->wagonable->weapons) {
            $isWeapons = true;
            break;
        }
    }
?>
<x-bladewind.tab-group name="workshop" color="black">
    <x-slot name="headings">
        <x-bladewind.tab-heading
            name="locomotive"
            label="Locomotive" />
        <x-bladewind.tab-heading
            name="wagon"
            label="Wagons"
        />
        @if($isWeapons)
            <x-bladewind.tab-heading
                name="weapon"
                label="Weapons"
            />
        @endif
    </x-slot>
    <x-bladewind.tab-body>
        <x-bladewind.tab-content name="locomotive">
            @include('town.partials.upgrade.locomotive')
        </x-bladewind.tab-content>
        <x-bladewind.tab-content name="wagon">
            <x-bladewind.tab-group name="wagons">
                <x-slot name="headings">
                    @foreach($player->train->wagons as $wagon)
                        <x-bladewind.tab-heading
                            name="wagon{{$wagon->id}}"
                            label="{{$wagon->name}}" />
                    @endforeach
                </x-slot>
                <x-bladewind.tab-body>
                    @foreach($player->train->wagons as $wagon)
                        <x-bladewind.tab-content name="wagon{{$wagon->id}}">
                            @include('town.partials.upgrade.wagons')
                        </x-bladewind.tab-content>
                    @endforeach
                </x-bladewind.tab-body>
            </x-bladewind.tab-group>
        </x-bladewind.tab-content>
        @if($isWeapons)
            <x-bladewind.tab-content name="weapon">
                @include('town.partials.upgrade.weapons')
            </x-bladewind.tab-content>
        @endif
    </x-bladewind.tab-body>
</x-bladewind.tab-group>
