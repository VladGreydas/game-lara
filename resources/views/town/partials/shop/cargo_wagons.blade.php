<?php
    $wagons = \App\Models\Wagon::factory()->makeMultipleShopCargoWagons();
?>
<script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
<link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet"/>

<div class="mt-6 bg-white shadow-sm rounded-lg divide-y w-full">
    <x-bladewind.table>
        <x-slot name="header">
            <th class="text-white p-4">Name</th>
            <th class="text-white p-4">Weight</th>
            <th class="text-white p-4">Armor</th>
            <th class="text-white p-4">Capacity</th>
            <th class="text-white p-4">Upgrade Cost</th>
            <th class="text-white p-4">Price</th>
            <th class="text-white p-4">Action</th>
        </x-slot>
        @foreach($wagons as $wagon)
            <tr>
                <td class="p-4 font-semibold">{{$wagon['name']}}</td>
                <td class="text-center">{{$wagon['weight'] }}t</td>
                <td class="text-center">{{$wagon['armor'] }}</td>
                <td class="text-center">{{$wagon['capacity'] }}</td>
                <td class="text-center">{{$wagon['upgrade_cost'] }}</td>
                <td class="text-center">{{$wagon['price'] }}</td>
                <td class="text-center">
                    <form method="post" action="{{route('wagon.purchase', $player->train)}}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="wagon" value="{{json_encode($wagon)}}">
                        <x-primary-button class="m-1">{{ __('Buy') }}</x-primary-button>
                    </form>
                </td>
            </tr>
        @endforeach
    </x-bladewind.table>
</div>
