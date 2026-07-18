<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-3xl text-[#5d3a1a]">
            {{ __('city.workshop') . ' — ' . $city->name }}
        </h2>
        <p class="font-serif text-[#8b5a2b] italic">"{{ __('city.progress_but_with_dignity') }}"</p>
        <a href="{{ route('city.show', $city) }}" class="victorian-btn inline-block mt-2 py-2 px-4 rounded text-sm">
            {{ __('city.back_to_city') }}
        </a>
        <x-player-info/>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl space-y-8">
        @if(session('success'))
            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded text-green-800 font-semibold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded text-red-800 font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <div class="victorian-card">
            <div class="p-6 border-b border-[#c5a059] bg-[#f5e6c8]">
                <h3 class="text-2xl font-bold text-[#5d3a1a] mb-2 font-serif">{{ __('city.train_equipment') }}</h3>
            </div>
            <div class="p-6">
                <section class="mb-6">
                    <x-locomotive-card :locomotive="$locomotive" :rename="false" :upgrade="true"/>
                </section>

                <section class="mb-6">
                    <div class="mt-4 p-4 border border-[#d4b483] rounded shadow-sm bg-white">
                        <h3 class="text-xl font-bold mb-2 text-[#5d3a1a] font-serif">🚃 {{ __('city.wagons') }}</h3>
                        @foreach($wagons as $wagon)
                            <x-wagon-card :wagon="$wagon" :rename="false" :upgrade="true"/>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>

    <style>
        .victorian-card {
            background: #fff8e7;
            border: 2px solid #c5a059;
            box-shadow: 4px 4px 12px rgba(93, 58, 26, 0.15);
        }
        .victorian-btn {
            background: #8b5a2b;
            color: #fff8e7;
            border: 2px solid #c5a059;
            transition: all 0.2s ease;
        }
        .victorian-btn:hover {
            background: #6d4a24;
            transform: translateY(-1px);
            box-shadow: 2px 2px 6px rgba(93, 58, 26, 0.2);
        }
    </style>
</x-app-layout>
