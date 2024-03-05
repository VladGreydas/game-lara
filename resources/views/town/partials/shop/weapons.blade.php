<?php
$weapons = \App\Models\Weapon::factory()->makeMultipleShopWeapons();
$iterator = 1;
?>
<script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
<link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet"/>

<div class="mt-6 bg-white shadow-sm rounded-lg divide-y w-full">
    <x-bladewind.table divider="thin">
        <x-slot name="header">
            <th class="text-white p-4">Name</th>
            <th class="text-white p-4">Type</th>
            <th class="text-white p-4">Damage</th>
            <th class="text-white p-4">Upgrade Cost</th>
            <th class="text-white p-4">Price</th>
            <th class="text-white p-4">Action</th>
        </x-slot>
        @foreach($weapons as $weapon)
            <tr class="border-slate-800 border-b-2">
                <td class="p-4 font-semibold">{{$weapon->name}}</td>
                <td class="text-center">{{$weapon->type }}</td>
                <td class="text-center">{{$weapon->damage }}</td>
                <td class="text-center">{{$weapon->upgrade_cost }}</td>
                <td class="text-center">{{$weapon->price }}</td>
                <td class="text-center">
                    <x-bladewind.button onclick="showModal('action-modal-{{$iterator}}')" class="text-white w-9/12 h-full p-2 bg-slate-800">
                        {{__('Buy')}}
                    </x-bladewind.button>
                    <x-bladewind.modal
                        backdrop_can_close="false"
                        name="action-modal-{{$iterator}}"
                        ok_button_label=""
                    >
                        <form method="post" action="{{route('weapon.purchase', $player->train)}}" class="flex flex-col flex-wrap items-center">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="weapon" value="{{json_encode($weapon)}}">
                            Choose which wagon to mount {{$weapon->name}} on
                            @foreach($player->train->getWeaponWagons() as $weapon_wagon)
                                <x-bladewind.radio-button
                                    color="black"
                                    name="wagon"
                                    label="{{$weapon_wagon->wagon->name}}"
                                    value="{{$weapon_wagon->id}}"
                                />
                            @endforeach
                            <x-bladewind.button can_submit="true" class="w-9/12 m-2 p-2 h-10 bg-slate-800 text-white">
                                Buy
                            </x-bladewind.button>
                        </form>
                    </x-bladewind.modal>
                </td>
            </tr>
            <?php $iterator++?>
        @endforeach
    </x-bladewind.table>
</div>
