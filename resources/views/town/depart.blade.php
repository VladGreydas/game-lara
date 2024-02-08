<x-app-layout>
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
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
                <th class="text-white p-4">Workshop</th>
                <th class="text-white p-4">Rail Shop</th>
                <th class="text-white p-4">Shop</th>
                <th class="text-white p-4">Saloon</th>
                <th class="text-white p-4">Fuel Cost</th>
                <th class="text-white p-4">Action</th>
            </x-slot>
            @foreach($destinations as $destination)
                <tr>
                    <td class="p-4 font-semibold">{{$destination['town']->name}}</td>
                    <td class="text-center">{{$destination['town']->workshop == 1 ? '+' : '-'}}</td>
                    <td class="text-center">{{$destination['town']->rail_shop == 1 ? '+' : '-'}}</td>
                    <td class="text-center">{{$destination['town']->shop == 1 ? '+' : '-'}}</td>
                    <td class="text-center">{{$destination['town']->saloon == 1 ? '+' : '-'}}</td>
                    <td class="text-center">{{$destination['fuel_cost']}}</td>
                    <td class="text-center">
                        <form method="post" action="{{route('town.travel', [
                                'player' => $player,
                                'town_id' => $destination['town']->id,
                                'cost' => $destination['fuel_cost']])}}">
                        </form>
                        <x-bladewind.button onclick="showModal('action-modal-{{$destination['town']->id}}')" class="text-white w-9/12 h-full p-2 bg-slate-800">
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
        <div class="p-6 rounded-lg divide-y">
            <a href="{{url()->previous()}}"><x-secondary-button>{{__('Back')}}</x-secondary-button></a>
        </div>
    </div>
</x-app-layout>
