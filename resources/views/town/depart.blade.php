<x-app-layout>
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Select destination') }}
            </h2>
        </div>
    </x-slot>
    <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y">
        <x-bladewind.table>
            <x-slot name="header">
                <th class="text-white p-4">Town</th>
                <th class="text-white p-4">Fuel Cost</th>
                <th class="text-white p-4">Depart</th>
            </x-slot>
            @foreach($destinations as $destination)
                <tr>
                    <td class="p-4 font-semibold">{{$destination['town']->name}}</td>
                    <td>{{$destination['fuel_cost']}}</td>
                    <td>
                        <form method="post" action="{{route('town.travel', [
                                'player' => $player,
                                'town_id' => $destination['town']->id,
                                'cost' => $destination['fuel_cost']])}}">

                        </form>
                        <x-bladewind.button onclick="showModal('action-modal-{{$destination['town']->id}}')" color="black">
                            {{__('Depart')}}
                        </x-bladewind.button>
                        <x-bladewind::modal
                            backdrop_can_close="false"
                            name="action-modal-{{$destination['town']->id}}"
                            ok_button_label=""
                            cancel_button_label="Stay here">

                            <form method="post" action="{{route('town.travel')}}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="player" value="{{$player[0]->id}}">
                                <input type="hidden" name="town_id" value="{{$destination['town']->id}}">
                                <input type="hidden" name="cost" value="{{$destination['fuel_cost']}}">
                                Are you sure you want to go to {{$destination['town']->name}}?
                                <x-bladewind::button can_submit="true">
                                    Go
                                </x-bladewind::button>
                            </form>
                        </x-bladewind::modal>
                    </td>
                </tr>
            @endforeach
        </x-bladewind.table>
    </div>
</x-app-layout>
