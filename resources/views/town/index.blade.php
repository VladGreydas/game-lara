<x-app-layout>
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <?php $town = $town[0];?>
    <?php $player = $player[0]?>

    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $town->name }}
            </h2>
        </div>
    </x-slot>
    <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y">
        <div class="p-6 flex-col">
            @include('town.partials.town-info')
            <div class="mt-6 rounded-lg divide-y">
                <a href="{{ route('town.depart', $town) }}"><x-primary-button>{{__('Travel')}}</x-primary-button></a>
            </div>
            @if(session('status') === 'upgrade-successful')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-green-800 text-lg">
                    {{__('Upgraded successfully')}}
                </p>
            @elseif(session('status') === 'upgrade-failed')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-lg text-red-800">
                    {{__("Can't upgrade, not enough money")}}
                </p>
            @endif
        </div>
    </div>
    <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y">
        <x-bladewind.tab-group name="town-info" color="black">
            <x-slot name="headings">
                @if($town->workshop)
                    <x-bladewind.tab-heading
                        name="workshop"
                        label="Workshop" />
                @endif
            </x-slot>
            <x-bladewind.tab-body>
                @if($town->workshop)
                    <x-bladewind.tab-content name="workshop">
                        @include('town.partials.workshop')
                    </x-bladewind.tab-content>
                @endif
            </x-bladewind.tab-body>
        </x-bladewind.tab-group>
    </div>
</x-app-layout>
