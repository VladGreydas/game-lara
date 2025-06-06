<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Workshop - ') . $city->name }}
        </h2>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl bg-white shadow-sm rounded-lg p-6 space-y-4">
        @if(session('success'))
            <div class="text-green-600 font-semibold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="text-red-600 font-semibold">{{ session('error') }}</div>
        @endif
        <section class="mb-6">
            <x-locomotive-card :locomotive="$locomotive" :rename="false" :upgrade="true"/>

        </section>

        <section class="mb-6">
            <div class="mt-4 p-4 border rounded-lg shadow bg-gray-50">
                <h3 class="text-lg font-bold mb-2">🚃 Wagons</h3>
                @foreach($wagons as $wagon)
                    <x-wagon-card :wagon="$wagon" :rename="false" :upgrade="true"/>
                @endforeach
            </div>
        </section>
    </div>

</x-app-layout>
