<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-3xl text-[#e5e5e5]">
            {{ __('workshop.workshop') . ' — ' . $city->name }}
        </h2>
        <p class="font-serif text-[#eab308] italic">"{{ __('common.progress_but_with_dignity') }}"</p>
        <a href="{{ route('city.show', $city) }}" class="industrial-btn inline-block mt-2 py-2 px-4 rounded text-sm">
            {{ __('workshop.back_to_city') }}
        </a>
        <x-player-info/>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl space-y-8">
        @if(session('success'))
            <div class="mt-6 p-4 bg-[#14532c] border border-green-200 rounded text-green-800 font-semibold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mt-6 p-4 bg-[#7f1d1d] border border-red-200 rounded text-red-800 font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <div class="industrial-card">
            <div class="p-6 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                <h3 class="text-2xl font-bold text-[#e5e5e5] mb-2 font-serif">{{ __('train.train_equipment') }}</h3>
            </div>
            <div class="p-6">
                <section class="mb-6">
                    <x-locomotive-card :locomotive="$locomotive" :rename="false" :upgrade="true"/>
                </section>

                <section class="mb-6">
                    <div class="mt-4 p-4 border border-[#d4b483] rounded shadow-sm bg-[#1f1f1f]">
                        <h3 class="text-xl font-bold mb-2 text-[#e5e5e5] font-serif">🚃 {{ __('wagon.wagons') }}</h3>
                        @foreach($wagons as $wagon)
                            <x-wagon-card :wagon="$wagon" :rename="false" :upgrade="true"/>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
