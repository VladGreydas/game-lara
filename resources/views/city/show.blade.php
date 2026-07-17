<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-3xl text-[#5d3a1a]">
            {{ __('city.current_city') . ' ' . $city->name }}
        </h2>
        <p class="font-serif text-[#8b5a2b] italic">"{{ __('city.progress_but_with_dignity') }}"</p>
        <x-player-info/>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl space-y-8">
        <!-- Main Header Card -->
        <div class="victorian-card">
            <div class="p-6 border-b border-[#c5a059] bg-[#f5e6c8]">
                <h3 class="text-2xl font-bold text-[#5d3a1a] mb-2 font-serif">{{ __('city.welcome_to') . ' ' . $city->name . '!' }}</h3>
                <p class="text-gray-700 italic">{{ __('city.refuel_locomotive') }}</p>
            </div>

            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- City Level -->
                    <div class="p-4 bg-white border border-[#d4b483] rounded">
                        <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('city.city_level') }}</h4>
                        <p class="text-gray-800 mb-2">
                            {{ $city->level }} / {{ $city->max_level }}
                        </p>
                        <p class="text-sm text-gray-600 mb-4">{{ __('city.upgrade_cost') }} ${{ $city->getUpgradeCost() }}</p>

                        @if($city->level < $city->max_level)
                            <form action="{{ route('city.upgrade') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                                    {{ __('city.upgrade_city') }}
                                </button>
                            </form>
                        @else
                            <p class="text-gray-600 mt-2 italic">{{ __('city.city_is_at_max_level') }}</p>
                        @endif
                    </div>

                    <!-- Refuel -->
                    <div class="p-4 bg-white border border-[#d4b483] rounded">
                        <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('city.locomotive_fuel') }}</h4>
                        <p class="text-gray-800 mb-2">
                            {{ $player->train->locomotive->fuel }} / {{ $player->train->locomotive->max_fuel }}
                        </p>
                        <p class="text-sm text-gray-600 mb-4">{{ __('city.refueling_cost') }} ${{ 2 * ($player->train->locomotive->max_fuel - $player->train->locomotive->fuel) }}</p>

                        @if($player->train->locomotive->fuel < $player->train->locomotive->max_fuel)
                            <form action="{{ route('city.refuel') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                                    {{ __('city.refuel') }}
                                </button>
                            </form>
                        @else
                            <p class="text-gray-600 mt-2 italic">{{ __('city.fuel_tank_is_full') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Outgoing Routes -->
        <div class="victorian-card">
            <div class="p-6 border-b border-[#c5a059] bg-[#f5e6c8]">
                <h3 class="text-2xl font-bold text-[#5d3a1a] font-serif">{{ __('city.outgoing_routes') }}</h3>
            </div>
            <div class="p-6">
                @if($city->outgoingRoutes->count())
                    <ul class="space-y-4">
                        @foreach($city->outgoingRoutes as $route)
                            <li class="p-4 bg-white border border-[#d4b483] rounded shadow-sm flex flex-col md:flex-row md:items-center justify-between">
                                <div class="mb-2 md:mb-0">
                                    <span class="font-bold text-[#5d3a1a] text-lg">→ {{ $route->toCity->name }}</span>
                                    <span class="ml-2 text-gray-600 text-sm italic">({{ __('city.fuel') }} {{ $route->fuel_cost }}, {{ __('city.time') }} {{ $route->travel_time }}h)</span>
                                </div>
                                <form method="POST" action="{{ route('city.travel', $route) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                                        {{ __('city.travel') }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-600 mt-2 italic">{{ __('city.no_outgoing_routes') }}</p>
                @endif
            </div>
        </div>

        <!-- Services -->
        <div class="grid md:grid-cols-3 gap-6 mt-8">
            @if ($player->city->has_workshop)
                <div class="victorian-card">
                    <div class="p-4 border-b border-[#c5a059] bg-[#f5e6c8]">
                        <h3 class="text-xl font-bold text-[#5d3a1a] font-serif">{{ __('city.workshop') }}</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 mb-4">{{ __('city.upgrade_train') }}</p>
                        <a href="{{ route('workshop.index') }}" class="victorian-btn inline-block w-full text-center py-2 px-4 rounded text-sm">
                            {{ __('city.go_to_workshop') }}
                        </a>
                    </div>
                </div>
            @endif

            @if ($player->city->has_shop)
                <div class="victorian-card">
                    <div class="p-4 border-b border-[#c5a059] bg-[#f5e6c8]">
                        <h3 class="text-xl font-bold text-[#5d3a1a] font-serif">{{ __('city.shop') }}</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 mb-4">{{ __('city.buy_sell_locomotives') }}</p>
                        <a href="{{ route('shop.index') }}" class="victorian-btn inline-block w-full text-center py-2 px-4 rounded text-sm">
                            {{ __('city.go_to_shop') }}
                        </a>
                    </div>
                </div>
            @endif

            @if ($player->city->hasSaloon())
                <div class="victorian-card">
                    <div class="p-4 border-b border-[#c5a059] bg-[#f5e6c8]">
                        <h3 class="text-xl font-bold text-[#5d3a1a] font-serif">{{ __('city.tavern') }}</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 mb-4">{{ __('city.rest_place') }}</p>
                        <a href="{{ route('city.saloon.show', $player->city) }}" class="victorian-btn inline-block w-full text-center py-2 px-4 rounded text-sm">
                            {{ __('city.visit_tavern') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Session Messages -->
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

        <!-- Footer -->
        <x-victorian-footer />
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
