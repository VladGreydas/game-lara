<?php
$locomotives = \App\Models\Locomotive::factory()->makeMultipleShopLocomotives();
?>
<script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
<link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet"/>

<div class="mt-6 bg-white shadow-sm rounded-lg divide-y w-full">
    <x-bladewind.table>
        <x-slot name="header">
            <th class="text-white p-4">Name</th>
            <th class="text-white p-4">Weight</th>
            <th class="text-white p-4">Power</th>
            <th class="text-white p-4">Engine Type</th>
            <th class="text-white p-4">Armor</th>
            <th class="text-white p-4">Fuel</th>
            <th class="text-white p-4">Upgrade Cost</th>
            <th class="text-white p-4">Price</th>
            <th class="text-white p-4">Action</th>
        </x-slot>
        @foreach($locomotives as $locomotive)
            <tr>
                <td class="p-4 font-semibold">{{$locomotive->name}}</td>
                <td class="text-center">{{$locomotive->weight }}t</td>
                <td class="text-center">{{$locomotive->power }}hp</td>
                <td class="text-center">{{$locomotive->type }}</td>
                <td class="text-center">{{$locomotive->armor }}</td>
                <td class="text-center">{{$locomotive->fuel }}</td>
                <td class="text-center">{{$locomotive->upgrade_cost }}</td>
                <td class="text-center">{{$locomotive->price }}</td>
                <td class="text-center">
                    <form method="post" action="{{route('locomotive.purchase', $player->train->locomotive)}}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="player" value="{{$player->id}}">
                        <input type="hidden" name="locom" value="{{$locomotive}}">
                        <x-primary-button>{{ __('Buy') }}</x-primary-button>
                    </form>
                </td>
            </tr>
        @endforeach
    </x-bladewind.table>
</div>
