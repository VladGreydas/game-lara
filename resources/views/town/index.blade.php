<x-app-layout>
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <?php $town = $town[0];?>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $town->name }}
            </h2>
        </div>
    </x-slot>
    <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y">
        <div class="p-6 flex-col">
            <p class="mt-4 text-lg text-gray-900">Workshop: {{ ($town->workshop ? 'Present' : 'Absent') }}</p>
            <p class="mt-4 text-lg text-gray-900">Rail Shop: {{ ($town->rail_shop ? 'Present' : 'Absent') }}</p>
            <p class="mt-4 text-lg text-gray-900">Resource Shop: {{ ($town->shop ? 'Present' : 'Absent') }}</p>
            <p class="mt-4 text-lg text-gray-900">Saloon: {{ ($town->saloon ? 'Present' : 'Absent') }}</p>
            <div class="mt-6 shadow-sm rounded-lg divide-y">
                <a href="{{ route('town.depart', $town) }}"><x-primary-button>{{__('Travel')}}</x-primary-button></a>
            </div>
        </div>
    </div>
</x-app-layout>
