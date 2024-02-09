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
            @include('town.partials.status')
        </div>
    </div>
    <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y">
        @include('town.partials.town-tabs')
    </div>
</x-app-layout>
