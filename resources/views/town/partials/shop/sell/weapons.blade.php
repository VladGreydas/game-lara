<?php
$weapon_wagons = $player->train->getWeaponWagons(false);
?>
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Choose the wagon') }}
</h2>
<x-bladewind.tab-group name="rail_shop_sell_weapon" color="black">
    <x-slot name="headings">
        @foreach($weapon_wagons as $weapon_wagon)
            <x-bladewind.tab-heading
                name="sell-weapon-wagon-{{$weapon_wagon->wagon->id}}"
                label="{{$weapon_wagon->wagon->name}}"
            />
        @endforeach
    </x-slot>
    <x-bladewind.tab-body>
        @foreach($weapon_wagons as $weapon_wagon)
            <x-bladewind.tab-content name="sell-weapon-wagon-{{$weapon_wagon->wagon->id}}">
                <x-bladewind.tab-group name="sell-weapons-wagon-{{$weapon_wagon->wagon->id}}">
                    <x-slot name="headings">
                        @foreach($weapon_wagon->weapons as $weapon)
                            <x-bladewind.tab-heading
                                name="sell-weapon-{{$weapon->id}}"
                                label="{{$weapon->name}}"/>
                        @endforeach
                    </x-slot>
                    <x-bladewind.tab-body>
                        @foreach($weapon_wagon->weapons as $weapon)
                            <x-bladewind.tab-content name="sell-weapon-{{$weapon->id}}">
                                <div class="p-6 ml-5 flex-col">
                                    <h3 class="p-5 font-semibold text-xl text-gray-800 leading-tight">
                                        {{ $weapon->name }}
                                    </h3>
                                    <div class="p-6 flex-col">
                                        <p class="mt-4 text-lg text-gray-900">Name: {{ $weapon->name }}</p>
                                        <p class="mt-4 text-lg text-gray-900">Level: {{ $weapon->lvl }}</p>
                                        <p class="mt-4 text-lg text-gray-900">Damage: {{ $weapon->damage }}</p>
                                        <p class="mt-4 text-lg text-gray-900">Type: {{ $weapon->type }}</p>
                                        <p class="mt-4 text-lg text-gray-900">Price: {{ $weapon->price / 2 }}</p>

                                        <x-bladewind.button onclick="showModal('modal-sell-weapon-{{$weapon->id}}')"
                                                            class="text-white w-2/5 h-full mt-4 p-2 bg-slate-800">
                                            {{__('Sell')}}
                                        </x-bladewind.button>
                                    </div>
                                    <x-bladewind.modal
                                        backdrop_can_close="false"
                                        name="modal-sell-weapon-{{$weapon->id}}"
                                        ok_button_label=""
                                    >
                                        <form method="post" action="{{route('weapon.sell', $weapon->id)}}"
                                              class="flex flex-col flex-wrap items-center">
                                            @csrf
                                            @method('DELETE')
                                            Are you sure you want to sell {{$weapon->name}}?
                                            @if (count($weapon_wagon->weapons) == 1)
                                                It's your last weapon in the wagon. You cannot defend without them. Proceed anyway?
                                            @endif
                                            <x-bladewind.button can_submit="true"
                                                                class="w-2/5 m-2 p-2 h-10 bg-slate-800 text-white">
                                                Sell
                                            </x-bladewind.button>
                                        </form>
                                    </x-bladewind.modal>
                                </div>
                            </x-bladewind.tab-content>
                        @endforeach
                    </x-bladewind.tab-body>
                </x-bladewind.tab-group>
            </x-bladewind.tab-content>
        @endforeach
    </x-bladewind.tab-body>
</x-bladewind.tab-group>
