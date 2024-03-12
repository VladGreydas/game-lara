<?php
$wagons = $player->train->wagons;

$cargo = $player->train->getCargoWagons();
$weapon = $player->train->getWeaponWagons();
?>

<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Choose the wagon') }}
</h2>

<x-bladewind.tab-group name="rail_shop_sell_wagon" color="black">
    <x-slot name="headings">
        @foreach($wagons as $wagon)
            <x-bladewind.tab-heading
                name="sell-wagon-{{$wagon->id}}"
                label="{{$wagon->name}}"
            />
        @endforeach
    </x-slot>
    <x-bladewind.tab-body>
        @foreach($wagons as $wagon)
            <x-bladewind.tab-content name="sell-wagon-{{$wagon->id}}">
                <div class="p-6 ml-5 flex-col">
                    <h3 class="p-5 font-semibold text-xl text-gray-800 leading-tight">
                        {{ $wagon->name }}
                    </h3>
                    <div class="p-6 flex-col">
                        <p class="mt-4 text-lg text-gray-900">Name: {{ $wagon->name }}</p>
                        <p class="mt-4 text-lg text-gray-900">Level: {{ $wagon->lvl }}</p>
                        <p class="mt-4 text-lg text-gray-900">Type: {{ $wagon->getType() }}</p>
                        <p class="mt-4 text-lg text-gray-900">Price: {{ $wagon->price / 2 }}</p>

                        <x-bladewind.button onclick="showModal('modal-sell-wagon-{{$wagon->id}}')"
                                            class="text-white w-2/5 h-full mt-4 p-2 bg-slate-800">
                            {{__('Sell')}}
                        </x-bladewind.button>
                    </div>
                    <x-bladewind.modal
                        backdrop_can_close="false"
                        name="modal-sell-wagon-{{$wagon->id}}"
                        ok_button_label=""
                    >
                        <form method="post" action="{{route('wagon.sell', $wagon->id)}}"
                              class="flex flex-col flex-wrap items-center font-semibold">
                            @csrf
                            @method('DELETE')
                            Are you sure you want to sell {{$wagon->name}}?
                            @if($wagon->getType() === 'WeaponWagon')
                                Weapons will be sold too (keeping weapons may be added in the future).
                                @if(count($weapon) == 1)
                                    <br/>It's your last weapon wagon in the train. You cannot defend without them. Proceed anyway?
                                @endif
                            @endif
                            @if ($wagon->getType() === 'CargoWagon')
                                All the resources will be transferred into other Cargo wagons (otherwise sold).
                                @if(count($cargo) == 1)
                                    <br/>It's your last cargo wagon in the train. You cannot transfer resources without them. Proceed anyway?
                                @endif
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
